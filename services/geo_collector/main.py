import os
import json
import logging
import asyncio
from fastapi import FastAPI
from pydantic import BaseModel
from typing import List, Optional, Union
from textblob import TextBlob
from confluent_kafka import Producer
from confluent_kafka.admin import AdminClient, NewTopic

# Logging setup
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI()

# Kafka configuration
KAFKA_BROKER = os.getenv("KAFKA_BROKER", "kafka:9092")
KAFKA_TOPIC = os.getenv("KAFKA_TOPIC", "geo_reviews")

# Kafka Producer initialization
producer_conf = {
    'bootstrap.servers': KAFKA_BROKER,
    'socket.timeout.ms': 10000,
    'message.timeout.ms': 10000
}
producer = Producer(producer_conf)

class Review(BaseModel):
    location_id: Union[int, None] = None
    source: str
    external_id: str
    author_name: str
    text: Optional[str]
    rating: int
    published_at: str

def analyze_sentiment(text: str) -> str:
    if not text:
        return "neutral"
    analysis = TextBlob(text)
    if analysis.sentiment.polarity > 0.1:
        return "positive"
    elif analysis.sentiment.polarity < -0.1:
        return "negative"
    else:
        return "neutral"

def delivery_report(err, msg):
    if err is not None:
        logger.error(f"Message delivery failed: {err}")
    else:
        logger.info(f"Message delivered to {msg.topic()} [{msg.partition()}]")

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

@app.on_event("startup")
async def startup_event():
    """
    Ensure Kafka topic exists on startup
    """
    logger.info("Starting Geo Collector Service...")
    asyncio.create_task(create_topic_with_retry(KAFKA_TOPIC))

@app.post("/collect_reviews")
def collect_reviews(reviews: List[Review]):
    """
    Endpoint for receiving reviews from external sources
    """
    processed_count = 0
    for review in reviews:
        sentiment = analyze_sentiment(review.text)

        # Use model_dump() for Pydantic v2 if available, else dict()
        if hasattr(review, 'model_dump'):
            message = review.model_dump()
        else:
            message = review.dict()

        message['sentiment'] = sentiment

        # Use external_id as key if location_id is not present
        key = str(review.location_id) if review.location_id is not None else str(review.external_id)

        # Send to Kafka
        producer.produce(
            KAFKA_TOPIC,
            key=key,
            value=json.dumps(message),
            callback=delivery_report
        )
        processed_count += 1

    producer.flush()
    return {"status": "success", "processed": processed_count}

@app.get("/health")
def health_check():
    return {"status": "ok"}

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8003)
