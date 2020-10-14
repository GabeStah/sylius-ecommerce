#!/bin/sh
echo "Pointing WHM subdomain ecommerce-testing.raritaneng.com to ecommerce/testing/${CI_COMMIT_SHORT_SHA}"
ssh -o StrictHostKeyChecking=no "centos@${DEPLOY_ENDPOINT}" -i ~/.ssh/id_rsa_root << EOF
  sudo whmapi1 delete_domain domain="ecommerce-testing.raritaneng.com"
  sudo whmapi1 create_subdomain domain="ecommerce-testing.raritaneng.com" document_root="ecommerce/testing/${CI_COMMIT_SHORT_SHA}"
  sudo whmapi1 start_autossl_check_for_one_user username=${REMOTE_USER}
EOF
