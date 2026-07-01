#!/usr/bin/env bash
set -euo pipefail

TAG="${1:-latest}"
PROJECT_NAME="${PROJECT_NAME:-cpmb}"
IMAGE_NAME="${IMAGE_NAME:-${PROJECT_NAME}-php}"
REGISTRY="${REGISTRY:-}"

if ! command -v docker >/dev/null 2>&1; then
    echo "Docker is not installed or not available in PATH."
    exit 1
fi

if ! docker compose version >/dev/null 2>&1; then
    echo "Docker Compose plugin is not available. Install docker-compose-plugin first."
    exit 1
fi

if [ -n "$REGISTRY" ]; then
    APP_IMAGE="${REGISTRY%/}/${IMAGE_NAME}"
else
    APP_IMAGE="${IMAGE_NAME}"
fi

mkdir -p \
    storage/app/public \
    storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache

if [ ! -f .env.docker ]; then
    cp .env.docker.example .env.docker
    echo ".env.docker created from .env.docker.example."
    echo "Edit .env.docker first, especially APP_KEY, APP_URL, DB_PASSWORD, then run ./deploy.sh again."
    exit 1
fi

if grep -q '^APP_KEY=$' .env.docker; then
    echo "APP_KEY is empty in .env.docker."
    echo "Generate one with: docker run --rm ${APP_IMAGE}:${TAG} php artisan key:generate --show"
    exit 1
fi

if ! docker image inspect "${APP_IMAGE}:${TAG}" >/dev/null 2>&1 && [ "${PULL_IMAGE:-false}" != "true" ]; then
    echo "Docker image ${APP_IMAGE}:${TAG} was not found locally."
    echo "Build it first with: ./build.sh ${TAG}"
    echo "Or set PULL_IMAGE=true if the image is stored in a registry."
    exit 1
fi

export APP_IMAGE
export IMAGE_TAG="$TAG"

if [ "${PULL_IMAGE:-false}" = "true" ]; then
    docker compose --env-file .env.docker pull
fi

docker compose --env-file .env.docker config >/dev/null
docker compose --env-file .env.docker up -d

echo "Deployment complete."
echo "App image: ${APP_IMAGE}:${TAG}"
echo "Open: http://localhost:${HTTP_PORT:-8080}"
