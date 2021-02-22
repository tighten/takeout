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

    async takeoutContainerIdsByShortNames(shortnames: string[]): Promise<string[]> {
      const containers = await this.listTakeoutContainers([])

      const ids = containers.filter((container: any) => {
        const containerName = container.Labels['com.tighten.takeout.shortname']
        // @TODO: Check for partial matches
        return shortnames.includes(containerName)
      }).map((container: any) => {
        return container.Id
      })

      return ids
    }

    listTakeoutContainers(status: string[]) {
      return this.docker.listContainers(
        {
          all: true,
          filters: {
            name: ['TO--'],
            status: status,
          },
        })
    }

    preEnableCheck() {
      // check if the image is downloaded
      console.log('in docker base')
    }

    async imageIsDownloaded(service: any, tag: string) {
      const downloadedImages = await this.docker.listImages()
      return downloadedImages.some((img: any) => img.RepoTags.includes(service.imageString(tag)))
    }

    downloadImage(service: any, tag: string) {
      return new Promise<void>((resolve, reject)  => {
        spinner.start(`Downloading ${service.imageString(tag)} image.`)
        return this.docker.pull(service.imageString(tag), (err: Error, stream: any) => {
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

          this.docker.modem.followProgress(stream, onFinished)
        })
      })
    }

    enableContainer(service: any, options: any) {
      this.docker.createContainer(options, (err: any, container: any) => {
        if (err) {
          throw new Error(err.message)
        }
        container.start({}, (err: any) => {
          if (err) throw err
          this.logSuccess(`${service.constructor.name} container started.`)
        })
      })
    }

    async disableContainer(id: string) {
      try {
        const container = this.docker.getContainer(id)
        const containerInspection = await container.inspect()
        container.remove({force: true}, (err: any, data: any) => {
          if (err) throw err
          this.logSuccess(`Container ${containerInspection.Name.substring(1)} successfully removed.`)
        })
      } catch (error) {
        this.logError(error)
      }
    }

    async stopContainer(id: string) {
      try {
        const container = this.docker.getContainer(id)
        const containerInspection = await container.inspect()
        container.stop((err: any, data: any) => {
          if (err) throw err
          this.logSuccess(`Container ${containerInspection.Name.substring(1)} successfully stopped.`)
        })
      } catch (error) {
        this.logError(error)
      }
    }
  }
}
