import DockerShell from '../shell/dockershell'
import spinner from '../ux/spinner'
const Docker = require('dockerode')

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

      this.docker = new Docker()
    }

    preEnableCheck() {
      // check if the image is downloaded
      console.log('in docker base')
    }

    async imageIsDownloaded(service: any, tag: string) {
      const downloadedImages = await this.docker.listImages()
      return downloadedImages.some(img => img.RepoTags.includes(service.imageString(tag)))
    }

    downloadImage(service: any, tag: string) {
      return new Promise((resolve, reject) => {
        spinner.start(`Downloading ${service.imageString(tag)} image.`)
        return this.docker.pull(service.imageString(tag), (err: Error, stream: any) => {
          // eslint-disable-next-line @typescript-eslint/no-use-before-define
          this.docker.modem.followProgress(stream, onFinished)
          if (err) {
            throw new Error(err.message)
          }

          function onFinished(err: any) {
            spinner.stop()
            if (err) {
              reject(new Error('There was a problem downloading the image.'))
            }
            resolve()
          }
        })
      })
    }

    enableContainer(service: any, options: any) {
      this.docker.createContainer(options, (err, container) => {
        if (err) {
          throw new Error(err.message)
        }
        container.start({}, (err, data) => {
          this.logSuccess(`${service.constructor.name} container started.`)
        })
      })
    }
  }
}
