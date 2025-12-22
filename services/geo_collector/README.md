# Geo Collector Service

This service is responsible for collecting reviews from various geo-services (like Google, Yelp, etc.) and publishing them to a Kafka topic for further processing by the main application.

## Purpose

- **Collect Reviews**: In a real-world scenario, this service would connect to external APIs (e.g., Google Places API) to fetch new reviews for registered business locations.
- **Analyze Sentiment**: Performs basic sentiment analysis on the review text to classify it as positive, neutral, or negative.
- **Publish to Kafka**: Pushes the collected and analyzed review data into the `geo_reviews` Kafka topic.

## API

### `POST /collect_reviews`

This endpoint allows for manually pushing review data into the system. It's primarily used for testing and can be used for integrations that push data instead of being pulled.

**Request Body:**

```json
[
  {
    "location_id": 1,
    "source": "google",
    "external_id": "unique-review-id-123",
    "author_name": "John Doe",
    "text": "This place is amazing!",
    "rating": 5,
    "published_at": "2023-10-27T10:00:00Z"
  }
]
```

**Response:**

```json
{
  "status": "success",
  "processed": 1
}
```

### `GET /health`

A simple health check endpoint. Returns `{"status": "ok"}` if the service is running.
