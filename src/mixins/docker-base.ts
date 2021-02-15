import DockerShell from '../shell/dockershell'

// eslint-disable-next-line valid-jsdoc

/**
 * Mixin to add initialization checks for
 * commands that have Docker requirements
 * in order to successfully complete.
 */

export default function dockerBaseMixin(className: any) {
  return class extends className {
    initializeCommand() {
      if (DockerShell.isNotInstalled()) {
        console.log('docker not installed')
      } else {
        console.log('docker is installed')
      }

      if (DockerShell.isDockerServiceRunning()) {
        console.log('docker is running')
      } else {
        throw new Error('docker is not running')
      }
    }
  }
}
