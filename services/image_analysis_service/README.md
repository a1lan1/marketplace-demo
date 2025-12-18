# Image Analysis Service

A Python/FastAPI microservice that asynchronously analyzes product images.

## Features
- **Kafka Integration**: Listens to `product_image_uploaded` topic.
- **Image Processing**: Simulates analysis (dimensions, tags, moderation).
- **Result Publishing**: Publishes results to `image_analysis_completed` topic.

## Verification

### 1. Check Logs
Monitor the service logs to see it processing messages:
```bash
docker compose logs -f image_analysis_service
```

### 2. Upload an Image
1. Go to Filament Admin Panel: [http://localhost:8585/admin/products](http://localhost:8585/admin/products)
2. Edit or Create a product.
3. Upload a Cover Image.
4. Save.

### 3. Verify Flow
1. **Laravel** produces `product_image_uploaded` event.
2. **This Service** consumes event, logs "Processing image...", and produces `image_analysis_completed`.
3. **Laravel Consumer** (`ImageAnalysisConsumer`) consumes result and logs "Dispatched ProcessImageAnalysisResult job".
4. **Laravel Horizon** (`ProcessImageAnalysisResult`) processes job and logs "Updating product X with image analysis results".

### API (Optional)
This service also exposes an HTTP endpoint for direct testing:
- **URL**: `http://localhost:8002/analyze_image/`
- **Method**: POST
- **Body**: `file` (image), `product_id` (int)
