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
        sh "RELEASE_VERSION=${releaseVersion} GITHUB_TOKEN=53951ecc51f64c62bfb2b2ba9dc9e7f75696b978 scripts/build.sh"
      }
    }

    stage("Deploy") {
      when { branch 'master' }
      steps {
        echo "The deployed version will be ${releaseVersion}"
        sh "RELEASE_VERSION=${releaseVersion} scripts/deploy.sh"

        slackSend (color: '#00FF00', channel: '#deployment', message: "[METRILO-WEBSITE] Deployed version: ${releaseVersion}")
      }
    }
  }

  post {
    success {
      slackSend (color: '#00FF00', channel: '#ci', message: "[METRILO-WEBSITE] SUCCESSFUL: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]' (${env.BUILD_URL})")
    }

    failure {
      slackSend (color: '#FF0000', channel: '#ci', message: "[METRILO-WEBSITE] FAILED: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]' (${env.BUILD_URL})")
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
