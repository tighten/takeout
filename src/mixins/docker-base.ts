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
        throw new Error('Docker is not installed.')
      }

      if (DockerShell.isDockerServiceStopped()) {
        throw new Error('Docker service is not running.')
      }
    }
  }
}
