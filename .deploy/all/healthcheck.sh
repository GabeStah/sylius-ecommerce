#!/bin/sh

# Add curl
apk add curl

echo "Performing healthcheck for: ${CI_ENVIRONMENT_URL}"

# Perform basic up health check
if curl -k -I "${CI_ENVIRONMENT_URL}" | grep "200 OK" > /dev/null; then
  echo "Health check passed."
  exit 0
else
  echo "Health check failed."
  exit 1
fi
