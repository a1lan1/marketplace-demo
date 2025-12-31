# Build stage
FROM golang:1.23-alpine AS builder

WORKDIR /app

# Download dependencies
COPY go.mod ./

COPY . .

# Build the application
RUN CGO_ENABLED=0 GOOS=linux go build -o currency-service ./cmd/api

# Run stage
FROM alpine:latest

WORKDIR /app

# Install certificates for HTTPS requests to external APIs
RUN apk --no-cache add ca-certificates

COPY --from=builder /app/currency-service .

EXPOSE 8004

CMD ["./currency-service"]
