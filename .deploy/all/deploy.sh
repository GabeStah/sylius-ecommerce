#!/bin/sh

# Compress src directory
echo "Compressing ${ARCHIVE_FILENAME}"
tar -zcf "${ARCHIVE_FILENAME}" .

# Create target directory
ssh -o StrictHostKeyChecking=no "${REMOTE_USER}"@"${DEPLOY_ENDPOINT}" << EOF
  echo "Creating target directory: ${TARGET_DIRECTORY}"
  mkdir -p ${TARGET_DIRECTORY}
EOF

# Transfer archive
echo "Transferring ${ARCHIVE_FILENAME}"
scp -o StrictHostKeyChecking=no "${ARCHIVE_FILENAME}" "${REMOTE_USER}@${DEPLOY_ENDPOINT}:${TARGET_DIRECTORY}"

# Extract archive
ssh -o StrictHostKeyChecking=no "${REMOTE_USER}"@"${DEPLOY_ENDPOINT}" << EOF
  cd ${TARGET_DIRECTORY}
  echo "Extracting ${ARCHIVE_FILENAME} to remote"
  tar -zxf ${ARCHIVE_FILENAME}
  echo "Removing ${ARCHIVE_FILENAME} from remote"
  rm ${ARCHIVE_FILENAME}
EOF

echo "Copying ${ENV_FILE} to .env."
scp -o StrictHostKeyChecking=no "${ENV_FILE}" "${REMOTE_USER}@${DEPLOY_ENDPOINT}:${TARGET_DIRECTORY}.env"

# Install submodule packages
ssh -o StrictHostKeyChecking=no "${REMOTE_USER}"@"${DEPLOY_ENDPOINT}" << EOF
  echo "Installing submodule Composer packages"
  cd "${TARGET_DIRECTORY}/packages/solarix/sylius-authorize-net-plugin"
  composer install
  cd "${TARGET_DIRECTORY}/packages/solarix/shipping"
  composer install
EOF

# Install Composer packages, Node packages, and rebuild
ssh -o StrictHostKeyChecking=no "${REMOTE_USER}"@"${DEPLOY_ENDPOINT}" << EOF
  cd ${TARGET_DIRECTORY}
  echo "Installing Composer packages"
  composer install
  echo "Installing Node packages"
  yarn install
  echo "Building assets"
  yarn build
  echo "Installing assets"
  php bin/console ckeditor:install --clear=skip
  php bin/console assets:install
  echo "Webpacking"
  yarn encore ${SYMFONY_ENVIRONMENT}
EOF
