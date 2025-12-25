from fastapi import FastAPI, UploadFile, File, Form
from PIL import Image
import io
import logging
import os
from confluent_kafka import Consumer, KafkaException, KafkaError, Producer
from confluent_kafka.admin import AdminClient, NewTopic
import asyncio
import json
from datetime import datetime
import requests # Import the requests library

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

app = FastAPI()

# Kafka Consumer configuration
KAFKA_BROKER = os.getenv("KAFKA_BROKER", "kafka:9092")
KAFKA_CONSUMER_GROUP_ID = os.getenv("KAFKA_CONSUMER_GROUP_ID", "image_analysis_service_group")
KAFKA_INPUT_TOPIC = "product_image_uploaded"
KAFKA_OUTPUT_TOPIC = "image_analysis_completed"

# Laravel App Host for internal communication
LARAVEL_APP_HOST = os.getenv("LARAVEL_APP_HOST", "app:8585") # Default to app:8585 for Docker Compose

consumer = None
consumer_task = None
producer = None

def get_kafka_producer():
    return Producer({'bootstrap.servers': KAFKA_BROKER})

async def create_topic_with_retry(topic_name):
    """
    Attempts to create a Kafka topic with retries.
    """
    admin_client = AdminClient({'bootstrap.servers': KAFKA_BROKER})
    topic_list = [NewTopic(topic_name, num_partitions=1, replication_factor=1)]

    for i in range(30): # Try for 60 seconds
        try:
            logger.info(f"Attempting to create topic {topic_name} (Attempt {i+1}/30)...")
            fs = admin_client.create_topics(topic_list)

            for topic, f in fs.items():
                try:
                    f.result()
                    logger.info(f"Topic {topic} created successfully")
                    return
                except Exception as e:
                    if "Topic with this name already exists" in str(e):
                         logger.info(f"Topic {topic} already exists")
                         return
                    else:
                        raise e
        except Exception as e:
            logger.warning(f"Failed to create topic {topic_name}: {e}. Retrying in 2 seconds...")
            await asyncio.sleep(2)

    logger.error(f"Failed to create topic {topic_name} after multiple attempts.")

async def consume_kafka_messages():
    global consumer, producer
    consumer_conf = {
        'bootstrap.servers': KAFKA_BROKER,
        'group.id': KAFKA_CONSUMER_GROUP_ID,
        'auto.offset.reset': 'earliest',
        'enable.auto.commit': True,
        'enable.auto.offset.store': False
    }
    consumer = Consumer(consumer_conf)
    producer = get_kafka_producer()

    try:
        consumer.subscribe([KAFKA_INPUT_TOPIC])
        logger.info(f"Kafka consumer subscribed to topic: {KAFKA_INPUT_TOPIC}")

        while True:
            msg = consumer.poll(timeout=1.0)

            if msg is None:
                continue
            if msg.error():
                if msg.error().code() == KafkaError._PARTITION_EOF:
                    logger.info(f"Reached end of partition {msg.topic()} [{msg.partition()}] at offset {msg.offset()}")
                elif msg.error().code() == KafkaError.UNKNOWN_TOPIC_OR_PART or msg.error().code() == 3:
                     logger.warning(f"Unknown topic: {msg.topic()} (Code {msg.error().code()}). Waiting for topic creation...")
                     await asyncio.sleep(2.0)
                elif msg.error():
                    raise KafkaException(msg.error())
            else:
                logger.info(f"Received message: Topic='{msg.topic()}', Key='{msg.key().decode('utf-8') if msg.key() else 'None'}': {msg.value().decode('utf-8')}")

                await process_image_upload_event(msg.key(), msg.value())

                consumer.store_offsets(msg)

            await asyncio.sleep(0.01)
    except Exception as e:
        logger.error(f"Error in Kafka consumer loop: {e}")
    finally:
        if consumer:
            consumer.close()
            logger.info("Kafka consumer closed.")

async def process_image_upload_event(key: bytes, value: bytes):
    """
    Processes image upload event from Kafka, analyzes the image, and publishes results.
    """
    try:
        data = json.loads(value.decode('utf-8'))
        product_id = data.get('product_id')
        image_url = data.get('image_url')

        if not product_id or not image_url:
            logger.error(f"Invalid image upload event data: {data}")
            return

        # Construct full URL using LARAVEL_APP_HOST if the URL is relative
        if image_url.startswith('/'):
            full_image_url = f"http://{LARAVEL_APP_HOST}{image_url}"
        else:
            full_image_url = image_url # Assume it's already a full URL if not relative

        logger.info(f"Processing image for product_id: {product_id} from URL: {full_image_url}")

        width = None
        height = None
        image_format = None
        tags = []
        moderation_status = "pending" # Default status

        try:
            # Download the image
            response = requests.get(full_image_url, stream=True, timeout=5)
            response.raise_for_status() # Raise an exception for HTTP errors (4xx or 5xx)

            # Open image with Pillow
            image = Image.open(io.BytesIO(response.content))

            width, height = image.size
            image_format = image.format

            # Simple tag generation based on dimensions and color mode
            if width and height:
                if width > height:
                    tags.append("landscape")
                elif height > width:
                    tags.append("portrait")
                else:
                    tags.append("square")

            if image.mode:
                tags.append(image.mode.lower()) # e.g., 'rgb', 'l' (grayscale)

            moderation_status = "approved" # For now, assume approved if analysis is successful

        except requests.exceptions.RequestException as req_err:
            logger.error(f"Failed to download image from {full_image_url}: {req_err}")
            tags.append("download_failed")
            moderation_status = "failed_download"
        except Image.UnidentifiedImageError:
            logger.error(f"Could not identify image from {full_image_url}. Invalid image format.")
            tags.append("invalid_format")
            moderation_status = "failed_format"
        except Exception as img_err:
            logger.error(f"Error processing image {full_image_url}: {img_err}")
            tags.append("processing_error")
            moderation_status = "failed_processing"

        analysis_results = {
            "product_id": product_id,
            "image_url": full_image_url,
            "width": width,
            "height": height,
            "format": image_format,
            "tags": tags,
            "moderation_status": moderation_status,
            "timestamp": datetime.now().isoformat()
        }

        # Publish analysis results to Kafka
        producer.produce(
            topic=KAFKA_OUTPUT_TOPIC,
            key=str(product_id).encode('utf-8'),
            value=json.dumps(analysis_results).encode('utf-8'),
            callback=delivery_report
        )
        producer.poll(0) # Trigger delivery report callbacks
        logger.info(f"Published image analysis results for product {product_id} to {KAFKA_OUTPUT_TOPIC}")

    except json.JSONDecodeError:
        logger.error(f"Failed to decode JSON from Kafka message: {value.decode('utf-8')}")
    except Exception as e:
        logger.error(f"Error processing image upload event: {e}")

def delivery_report(err, msg):
    """ Called once for each message produced to indicate delivery result. """
    if err is not None:
        logger.error(f"Message delivery failed: {err}")
    else:
        logger.info(f"Message delivered to {msg.topic()} [{msg.partition()}] at offset {msg.offset()}")

@app.on_event("startup")
async def startup_event():
    global consumer_task
    logger.info("Starting Kafka consumer task for image analysis service...")

    # Ensure output topic exists
    asyncio.create_task(create_topic_with_retry(KAFKA_OUTPUT_TOPIC))

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
    if producer:
        producer.flush() # Wait for any outstanding messages to be delivered
        logger.info("Kafka producer flushed.")

@app.post("/analyze_image/")
async def analyze_image_http(file: UploadFile = File(...), product_id: int = Form(...)):
    logger.info(f"Received HTTP image for analysis for product_id: {product_id}")
    try:
        image_data = await file.read()
        image = Image.open(io.BytesIO(image_data))

        width, height = image.size
        image_format = image.format

        tags = []
        if width and height:
            if width > height:
                tags.append("landscape")
            elif height > width:
                tags.append("portrait")
            else:
                tags.append("square")
        if image.mode:
            tags.append(image.mode.lower())

        analysis_results = {
            "product_id": product_id,
            "filename": file.filename,
            "content_type": file.content_type,
            "width": width,
            "height": height,
            "format": image_format,
            "tags": tags,
            "moderation_status": "approved_http" # Keep this distinct for HTTP path
        }
        logger.info(f"HTTP Analysis complete for {file.filename}: {analysis_results}")
        return analysis_results
    except Exception as e:
        logger.error(f"Error analyzing HTTP image: {e}")
        return {"error": str(e)}

@app.get("/")
async def root():
    return {"message": "Image Analysis Service is running!"}
