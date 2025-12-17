# NLP Search Preprocessing Service

A Python/FastAPI microservice for cleaning and enhancing search queries.

## Features
- **Preprocessing**: Lowercases and strips whitespace (extensible for lemmatization, spell-check, etc.).
- **FastAPI**: simple REST interface.

## Verification

### 1. Test Endpoint
Send a POST request with a raw query:

```bash
curl -X POST http://localhost:8001/preprocess_query \
  -H "Content-Type: application/json" \
  -d '{"query": "  LaRaVeL  "}'
```

Response:
```json
{"processed_query": "laravel"}
```
