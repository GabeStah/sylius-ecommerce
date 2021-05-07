/**
 * JetBrains Space Automation
 * This Kotlin-script file lets you automate build activities
 * For more info, see https://www.jetbrains.com/help/space/automation.html
 */

/**
 * STAGES
 *  deploy
 *  healthcheck
 *  release (main)
 */

/**
 * ALWAYS
 *  before_script.sh
 */



job("branch/others") {
  startOn {
    gitPush {
      branchFilter {
        // Non-main branches
        -"refs/heads/main"
      }
    }
  }

  container(displayName = "build", image = "node:alpine") {
    env["AWS_ACCESS_KEY_ID"] = Secrets("aws_access_key_id")
    env["AWS_REGION"] = Params("aws_region")
    env["AWS_SECRET_ACCESS_KEY"] = Secrets("aws_secret_access_key")
    env["CDN_DISTRIBUTION_ID"] = Secrets("cdn_distribution_id")
    env["S3_BUCKET_NAME"] = Params("s3_bucket_name")

    resources {
      cpu = 0.4.cpu
      memory = 1000.mb
    }
    shellScript {
      content = """
        echo Directory structure
        ls -R /mnt
        echo Working dir is
        pwd
        yarn install
        yarn build
        echo Build complete.
        echo Directory structure
        ls -R /mnt
        echo Working dir is
        pwd
        apk add --no-cache curl jq python py-pip
        pip install awscli
        aws s3 cp docs/.vuepress/dist/ s3://${'$'}S3_BUCKET_NAME/hermes/ --recursive --include "*"
        aws cloudfront create-invalidation --distribution-id ${'$'}CDN_DISTRIBUTION_ID --paths "/*"
        echo ${'$'}JB_SPACE_EXECUTION_NUMBER on ${'$'}JB_SPACE_GIT_BRANCH complete.
      """
    }
  }

}
