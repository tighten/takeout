import {
  DockerodeContainer,
} from '../types'
import DockerShell from '../shell/dockershell'
import Spinner from '../ux/spinner'
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

    async takeoutContainersByShortNames(shortnames: string[], status: string[]): Promise<DockerodeContainer[]> {
      const containers = await this.listTakeoutContainers(status)

      return containers.filter((container: any) => {
        const containerName = container.Labels['com.tighten.takeout.shortname']
        // @TODO: Check for partial matches
        return shortnames.includes(containerName)
      })
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

    async imageIsDownloaded(service: any, tag: string) {
      const downloadedImages = await this.docker.listImages()
      return downloadedImages.some((img: any) => img.RepoTags.includes(service.imageString(tag)))
    }

    downloadImage(service: any, tag: string) {
      return new Promise<void>((resolve, reject)  => {
        Spinner.start(`Downloading ${service.imageString(tag)} image.`)
        return this.docker.pull(service.imageString(tag), (err: Error, stream: any) => {
          if (err) {
            throw new Error(err.message)
          }

          function onFinished(err: any) {
            Spinner.stop()
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
      Spinner.start('Enabling container(s).')
      this.docker.createContainer(options, (err: any, container: any) => {
        if (err) {
          throw new Error(err.message)
        }
        container.start({}, (err: any) => {
          if (err) throw err
          Spinner.stop()
          this.logSuccess(`${service.constructor.name} container started.`)
        })
      })
    }

    async disableContainer(id: string) {
      const container = this.docker.getContainer(id)
      const containerInspection = await container.inspect()
      Spinner.start('Disabling container(s).')
      try {
        container.remove({force: true}, (err: any, data: any) => {
          if (err) throw err
          Spinner.stop()
          this.logSuccess(`Container ${containerInspection.Name.substring(1)} successfully removed.`)
        })
      } catch (error) {
        this.logError(error)
      }
    }

    async stopContainer(id: string) {
      const container = this.docker.getContainer(id)
      const containerInspection = await container.inspect()
      Spinner.start('Stopping container(s).')
      try {
        container.stop((err: any, data: any) => {
          if (err) throw err
          Spinner.stop()
          this.logSuccess(`Container ${containerInspection.Name.substring(1)} successfully removed.`)
        })
      } catch (error) {
        this.logError(error)
      }
    }
  }
}
