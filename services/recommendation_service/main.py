from fastapi import FastAPI
from confluent_kafka import Consumer, KafkaException, KafkaError
from clickhouse_driver import Client
import asyncio
import json
import logging
import os
from datetime import datetime

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

app = FastAPI()
logger.info("Starting Recommendation Service v2 (Auto-Topic-Creation Enabled)")

# Kafka Consumer configuration
KAFKA_BROKER = os.getenv("KAFKA_BROKER", "kafka:9092")
KAFKA_GROUP_ID = os.getenv("KAFKA_GROUP_ID", "recommendation_service_group")
KAFKA_TOPICS = os.getenv("KAFKA_TOPICS", "user_activity").split(',')

# ClickHouse configuration
CLICKHOUSE_HOST = os.getenv("CLICKHOUSE_HOST", "clickhouse")
CLICKHOUSE_PORT = int(os.getenv("CLICKHOUSE_PORT", "9000"))
CLICKHOUSE_USER = os.getenv("CLICKHOUSE_USER", "default")
CLICKHOUSE_PASSWORD = os.getenv("CLICKHOUSE_PASSWORD", "password")
CLICKHOUSE_DB = os.getenv("CLICKHOUSE_DB", "default")
CLICKHOUSE_TABLE = "user_activities"

consumer = None
consumer_task = None
clickhouse_client = None

def get_clickhouse_client():
    return Client(
        host=CLICKHOUSE_HOST,
        port=CLICKHOUSE_PORT,
        user=CLICKHOUSE_USER,
        password=CLICKHOUSE_PASSWORD,
        database=CLICKHOUSE_DB
    )

def create_clickhouse_table_if_not_exists():
    global clickhouse_client
    try:
        clickhouse_client = get_clickhouse_client()
        query = f"""
        CREATE TABLE IF NOT EXISTS {CLICKHOUSE_TABLE} (
            user_id Nullable(UInt64),
            event_type String,
            url String,
            ts DateTime,
            data String
        ) ENGINE = MergeTree()
        ORDER BY (ts)
        """
        clickhouse_client.execute(query)
        logger.info(f"ClickHouse table '{CLICKHOUSE_TABLE}' ensured to exist.")
    except Exception as e:
        logger.error(f"Failed to connect to ClickHouse or create table: {e}")
        raise

async def consume_kafka_messages():
    global consumer
    consumer_conf = {
        'bootstrap.servers': KAFKA_BROKER,
        'group.id': KAFKA_GROUP_ID,
        'auto.offset.reset': 'earliest',
        'enable.auto.commit': True,
        'enable.auto.offset.store': False, # Manual offset store for more control
        'allow.auto.create.topics': True
    }
    consumer = Consumer(consumer_conf)

    try:
        consumer.subscribe(KAFKA_TOPICS)
        logger.info(f"Kafka consumer subscribed to topics: {KAFKA_TOPICS}")

        while True:
            msg = consumer.poll(timeout=0) # Non-blocking poll

            if msg is None:
                await asyncio.sleep(0.1) # Sleep briefly if no message
                continue
            if msg.error():
                if msg.error().code() == KafkaError._PARTITION_EOF:
                    # End of partition event - not an error
                    logger.info(f"Reached end of partition {msg.topic()} [{msg.partition()}] at offset {msg.offset()}")
                elif msg.error().code() == KafkaError.UNKNOWN_TOPIC_OR_PART or msg.error().code() == 3:
                    logger.warning(f"Unknown topic: {msg.topic()} (Code {msg.error().code()}). Waiting for topic creation...")
                    await asyncio.sleep(2.0)
                elif msg.error():
                    raise KafkaException(msg.error())
            else:
                # Message successfully received
                logger.info(f"Received message: Topic='{msg.topic()}', Partition={msg.partition()}, Offset={msg.offset()}, Key='{msg.key().decode('utf-8') if msg.key() else 'None'}': {msg.value().decode('utf-8')}")

                # Process the message
                await process_user_activity(msg.topic(), msg.key(), msg.value())

                # Manually store offset
                consumer.store_offsets(msg)

            await asyncio.sleep(0.01) # Yield control to other tasks
    except Exception as e:
        logger.error(f"Error in Kafka consumer loop: {e}")
    finally:
        if consumer:
            consumer.close()
            logger.info("Kafka consumer closed.")

async def process_user_activity(topic: str, key: bytes, value: bytes):
    """
    Processes user activity data and stores it in ClickHouse.
    """
    global clickhouse_client
    if not clickhouse_client:
        try:
            clickhouse_client = get_clickhouse_client()
        except Exception as e:
            logger.error(f"Failed to get ClickHouse client in process_user_activity: {e}")
            return

    try:
        data = json.loads(value.decode('utf-8'))

        user_id = data.get('user_id')
        event_type = data.get('event_type')
        url = data.get('url')
        ts_str = data.get('ts')

        # Convert timestamp string to datetime object for ClickHouse
        ts = datetime.strptime(ts_str, '%Y-%m-%d %H:%M:%S') if ts_str else datetime.now()

        # Insert into ClickHouse
        insert_query = f"INSERT INTO {CLICKHOUSE_TABLE} (user_id, event_type, url, ts, data) VALUES"
        clickhouse_client.execute(insert_query, [[user_id, event_type, url, ts, json.dumps(data)]])

        logger.info(f"Activity stored in ClickHouse: User ID={user_id}, Event Type={event_type}, URL={url}")

    except json.JSONDecodeError:
        logger.error(f"Failed to decode JSON from Kafka message: {value.decode('utf-8')}")
    except Exception as e:
        logger.error(f"Error processing user activity or storing in ClickHouse: {e}")


@app.on_event("startup")
async def startup_event():
    global consumer_task
    logger.info("Connecting to ClickHouse and ensuring table exists...")
    create_clickhouse_table_if_not_exists()
    logger.info("Starting Kafka consumer task...")
    consumer_task = asyncio.create_task(consume_kafka_messages())

@app.on_event("shutdown")
async def shutdown_event():
    global consumer_task
    if consumer_task:
        logger.info("Cancelling Kafka consumer task...")
        consumer_task.cancel()
        try:
            await consumer_task
        except asyncio.CancelledError:
            logger.info("Kafka consumer task cancelled.")
    if consumer:
        consumer.close()
        logger.info("Kafka consumer closed during shutdown.")
    if clickhouse_client:
        # ClickHouse client doesn't have a formal close method in this driver version
        logger.info("ClickHouse client connection managed.")


@app.get("/")
async def root():
    return {"message": "Recommendation Service is running!"}

@app.get("/recommendations/{user_id}")
async def get_recommendations(user_id: int):
    global clickhouse_client
    if not clickhouse_client:
        try:
            clickhouse_client = get_clickhouse_client()
        except Exception as e:
            logger.error(f"Failed to get ClickHouse client for recommendations: {e}")
            return {"user_id": user_id, "recommendations": []}

    logger.info(f"Received recommendation request for user_id: {user_id}")

    user_recommendations = []
    general_recommendations = []

    try:
        # 1. Get recently viewed products by this user
        user_viewed_query = f"""
        SELECT DISTINCT
            toInt64OrNull(extract(url, '/catalog/([0-9]+)')) AS product_id
        FROM {CLICKHOUSE_TABLE}
        WHERE user_id = %(user_id)s
          AND event_type = 'page_view'
          AND url LIKE '%%/catalog/%%'
          AND product_id IS NOT NULL
        ORDER BY ts DESC
        LIMIT 5
        """
        raw_user_views = clickhouse_client.execute(user_viewed_query, {'user_id': user_id})
        user_recommendations = [row[0] for row in raw_user_views if row[0] is not None]
        logger.info(f"Recently viewed by user {user_id}: {user_recommendations}")

        # 2. Get overall most popular products
        # Exclude products already in user_recommendations
        exclude_ids = user_recommendations if user_recommendations else [0]

        popular_query = f"""
        SELECT
            product_id,
            count() AS views
        FROM (
            SELECT toInt64OrNull(extract(url, '/catalog/([0-9]+)')) AS product_id
            FROM {CLICKHOUSE_TABLE}
            WHERE event_type = 'page_view'
              AND url LIKE '%%/catalog/%%'
              AND product_id IS NOT NULL
        )
        WHERE product_id NOT IN %(exclude_ids)s
        GROUP BY product_id
        ORDER BY views DESC
        LIMIT 5
        """
        raw_popular_products = clickhouse_client.execute(popular_query, {'exclude_ids': exclude_ids})
        general_recommendations = [row[0] for row in raw_popular_products]
        logger.info(f"Most popular products (excluding user's views): {general_recommendations}")

        # Combine and ensure uniqueness
        final_recommendations = list(dict.fromkeys(user_recommendations + general_recommendations))

        return {"user_id": user_id, "recommendations": final_recommendations}
    except Exception as e:
        logger.error(f"Error fetching recommendations from ClickHouse for user {user_id}: {e}")
        # In case of error, return empty list
        return {"user_id": user_id, "recommendations": []}
