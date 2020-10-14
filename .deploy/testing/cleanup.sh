#!/bin/sh

# Remove all but the newest 5 releases
ssh -o StrictHostKeyChecking=no "centos@${DEPLOY_ENDPOINT}" -i ~/.ssh/id_rsa_root << EOF
  echo "Removing old releases"
  sudo find /home/raritaneng/ecommerce/testing -maxdepth 1 -type d -printf '%Ts\t%P\n' | sort -n | head -n -6 | cut -f 2- | xargs rm -rf
EOF
