#!/usr/bin/env bash
#
# Build and tag the FrankenPHP backend Docker image for ECR.

set -euo pipefail

ENV_FILE=.env.docker

[[ -f "$ENV_FILE" ]] || { echo "$ENV_FILE not found" >&2; exit 1; }

set -a
# shellcheck disable=SC1090
source "$ENV_FILE"
set +a

: "${AWS_REGION:?missing in $ENV_FILE}"
: "${ECR_REPOSITORY:?missing in $ENV_FILE}"
: "${VITE_REVERB_APP_KEY:?missing in $ENV_FILE}"
: "${VITE_REVERB_HOST:?missing in $ENV_FILE}"
: "${VITE_REVERB_PORT:?missing in $ENV_FILE}"
: "${VITE_REVERB_SCHEME:?missing in $ENV_FILE}"

ACCOUNT_ID=$(aws sts get-caller-identity --query Account --output text)
REGISTRY="${ACCOUNT_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com"
ECR_TAG="${REGISTRY}/${ECR_REPOSITORY}:frankenphp"

docker build \
    --file Dockerfile.frankenphp \
    --platform linux/amd64 \
    --provenance=false \
    --sbom=false \
    --secret id=vite_reverb_app_key,env=VITE_REVERB_APP_KEY \
    --build-arg VITE_REVERB_HOST \
    --build-arg VITE_REVERB_PORT \
    --build-arg VITE_REVERB_SCHEME \
    -t kurozora:frankenphp \
    -t "$ECR_TAG" \
    .

echo ""
echo ">>> Built and tagged: $ECR_TAG"
echo ">>> Push with: docker push $ECR_TAG"
