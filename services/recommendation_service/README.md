# Recommendation Service

A Python/FastAPI microservice for personalized product recommendations.

## Features
- **ClickHouse Integration**: Stores user activity events specific for high-speed querying.
- **Kafka Consumption**: Ingests `user_activity_page_view` and `user_activity_click`.
- **Hybrid Algorithm**: Combines recently viewed products with overall popular items.

## Verification

### 1. Generate Activity
Navigate around the frontend ([http://localhost:8585](http://localhost:8585)) as a logged-in user. View products to generate `page_view` events.

### 2. Check ClickHouse
Verify data is reaching the database:
```bash
docker compose exec clickhouse clickhouse-client --password password --query "SELECT * FROM user_activities LIMIT 5"
```

### 3. Get Recommendations
Fetch recommendations for a user (e.g., User ID 1):
```bash
curl http://localhost:8000/recommendations/1
```
Response:
```json
{
  "user_id": 1,
  "recommendations": [101, 25, 33]
}
```
