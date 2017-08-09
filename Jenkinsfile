pipeline {
  agent any

  stages {
    stage("Release") {
      when { branch 'MET-1688/magento2-deployment-volumes-are-overwritten-during-deploy' }
      steps {
        script { releaseVersion = buildReleaseVersion() }
        echo "The released version will be ${releaseVersion}"

        withCredentials([
          string(credentialsId: '85799b41-0d8f-4148-be77-978892f6cdc4', variable: 'GITHUB_TOKEN'),
          usernamePassword(credentialsId: 'magento2_store', usernameVariable: 'MAGE2_REPO_USERNAME', passwordVariable: 'MAGE2_REPO_PASSWORD')
        ]) {
          sh "RELEASE_VERSION=${releaseVersion} make build"
        }
      }
    }

    stage("Deploy") {
      when { branch 'MET-1688/magento2-deployment-volumes-are-overwritten-during-deploy' }
      steps {
        echo "The deployed version will be ${releaseVersion}"
        sh "RELEASE_VERSION=${releaseVersion} make deploy"

        slackSend (color: '#00FF00', channel: '#deployment', message: "[MAGENTO2-TESTENV] Deployed version: ${releaseVersion}")
      }
    }

    //
    // stage("Update plugin") {
    //   when { branch 'master' }
    //
    //   steps {
    //     script { releaseVersion = buildReleaseVersion() }
    //     sh 'make update_plugin'
    //     slackSend (color: '#00FF00', channel: '#deployment', message: "[MAGENTO2-TESTENV] Deployed version: ${releaseVersion}")
    //   }
    // }
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
