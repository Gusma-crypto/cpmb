#!/usr/bin/env bash
set -euo pipefail

TAG="${1:-latest}"
PROJECT_NAME="${PROJECT_NAME:-cpmb}"
IMAGE_NAME="${IMAGE_NAME:-${PROJECT_NAME}-php}"
REGISTRY="${REGISTRY:-}"
DOCKERFILE="${DOCKERFILE:-docker/php/Dockerfile}"
BUILD_PROGRESS="${BUILD_PROGRESS:-plain}"

if ! command -v docker >/dev/null 2>&1; then
    echo "Docker is not installed or not available in PATH."
    exit 1
fi

if [ ! -f "$DOCKERFILE" ]; then
    echo "Dockerfile not found: $DOCKERFILE"
    exit 1
fi

if [ -n "$REGISTRY" ]; then
    APP_IMAGE="${REGISTRY%/}/${IMAGE_NAME}"
else
    APP_IMAGE="${IMAGE_NAME}"
fi

echo "Building ${APP_IMAGE}:${TAG}"
docker build --progress="$BUILD_PROGRESS" -f "$DOCKERFILE" -t "${APP_IMAGE}:${TAG}" .

if [ "$TAG" != "latest" ]; then
    docker tag "${APP_IMAGE}:${TAG}" "${APP_IMAGE}:latest"
fi

if [ "${PUSH_IMAGE:-false}" = "true" ]; then
    echo "Pushing ${APP_IMAGE}:${TAG}"
    docker push "${APP_IMAGE}:${TAG}"

    if [ "$TAG" != "latest" ]; then
        echo "Pushing ${APP_IMAGE}:latest"
        docker push "${APP_IMAGE}:latest"
    fi
fi

echo "Done: ${APP_IMAGE}:${TAG}"
