pipeline {
  agent any

  stages {
    stage("Release") {
      when { branch 'master' }
      steps {
        script {
          releaseVersion = buildReleaseVersion()
        }
        echo "The released version will be ${releaseVersion}"
        withCredentials(
          [
            string(credentialsId: '85799b41-0d8f-4148-be77-978892f6cdc4', variable: 'GITHUB_TOKEN'),
            usernamePassword(credentialsId: 'magento2_store', usernameVariable: 'MAGE_DEV_UNAME', passwordVariable: 'MAGE_DEV_PASSWORD')
          ]
        ) {
          sh "RELEASE_VERSION=${releaseVersion} GITHUB_TOKEN=$GITHUB_TOKEN MAGE_DEV_UNAME=$MAGE_DEV_UNAME MAGE_DEV_PASSWORD=$MAGE_DEV_PASSWORD scripts/build.sh"
        }
      }
    }

    stage("Deploy") {
      when { branch 'master' }
      steps {
        echo "The deployed version will be ${releaseVersion}"
        sh "RELEASE_VERSION=${releaseVersion} scripts/deploy.sh"

        slackSend (color: '#00FF00', channel: '#deployment', message: "[MAGENTO2-TESTENV] Deployed version: ${releaseVersion}")
      }
    }
  }

  post {
    success {
      slackSend (color: '#00FF00', channel: '#ci', message: "[MAGENTO2-TESTENV] SUCCESSFUL: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]' (${env.BUILD_URL})")
    }

    failure {
      slackSend (color: '#FF0000', channel: '#ci', message: "[MAGENTO2-TESTENV] FAILED: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]' (${env.BUILD_URL})")
    }
  }
}

def buildReleaseVersion() {
  releaseVersionRaw = sh (
    script: 'cat .release-version',
    returnStdout: true
  )

  releaseVersionRaw.trim()
}
