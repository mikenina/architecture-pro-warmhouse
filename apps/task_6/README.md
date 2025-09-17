# Device Microservice Prototype

## Prerequisites

- Docker and Docker Compose

## Getting Started

### Option 1: Using Docker Compose

```bash
cd device_api_gateway
docker-compose up --build -d
cd ..
cd device_microservice
docker-compose up --build -d
```

This script will:

1. Build and start the Device API Gateway - entrypoint to communicate with external devices
2. Build and start the Device Microservice with the PostgreSQL and application containers

## API Testing

Now Device Microservice API will be available at http://localhost:8080

## API Endpoints

- `POST /api/v1/device/` - Create device
```bash
  curl --request POST \
  --url http://localhost:8080/api/v1/device/ \
  --header 'Content-Type: application/json' \
  --data '{
  "serial_number": "348109751",
  "device_type_id": 1,
  "user_id": 2,
  "location_id": 3,
  "description": "Heating System SN 3481097512 at location #3"
  }'
```
- `GET /api/v1/device/list?userId=2` - Get all devices of the user 2
```bash
curl --request GET \
  --url 'http://localhost:8080/api/v1/device/list?userId=2' \
```
- `POST /api/v1/device/1/healthcheck` - Check and update health status of the device_id = 1
```bash
curl --request POST \
  --url http://localhost:8080/api/v1/device/1/healthcheck \
```
