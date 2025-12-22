import os
import json
import logging
from fastapi import FastAPI
from pydantic import BaseModel
from typing import List, Optional
from textblob import TextBlob
from confluent_kafka import Producer

# Logging setup
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI()

# Kafka configuration
KAFKA_BROKER = os.getenv("KAFKA_BROKER", "kafka:9092")
KAFKA_TOPIC = os.getenv("KAFKA_TOPIC", "geo_reviews")

# Kafka Producer initialization
producer_conf = {'bootstrap.servers': KAFKA_BROKER}
producer = Producer(producer_conf)

class Review(BaseModel):
    location_id: int
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

@app.post("/collect_reviews")
def collect_reviews(reviews: List[Review]):
    """
    Endpoint for receiving reviews from external sources
    """
    processed_count = 0
    for review in reviews:
        sentiment = analyze_sentiment(review.text)

        message = review.dict()
        message['sentiment'] = sentiment

        # Send to Kafka
        producer.produce(
            KAFKA_TOPIC,
            key=str(review.location_id),
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
