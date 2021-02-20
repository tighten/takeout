import Command from '../commands/base'
import {flags} from '@oclif/command'
import inquirer = require('inquirer')
import {availableServices, serviceByShortName} from '../helpers'
import dockerBaseMixin from '../mixins/docker-base'

export default class Enable extends dockerBaseMixin(Command) {
  static description = 'Enable services via Takeout'

  /** Allow multiple services (argv) to be enabled at once */
  static strict = false

  static flags = {
    help: flags.help({char: 'h'}),
  }

  async run() {
    const {argv} = this.parse(Enable)

    this.initializeCommand()

    let selectedServices

    if (argv?.length) {
      selectedServices = argv
    } else {
      const responses = await inquirer.prompt([
        {
          type: 'checkbox',
          name: 'services',
          message: 'Takeout containers to enable.',
          choices: availableServices,
        },
      ])
      selectedServices = responses.services
    }

    const selectedServiceClasses = selectedServices.map((service: string) => {
      return serviceByShortName(service)
    })

    for (const Service of selectedServiceClasses) {
      const serviceInstance = new Service()

      // eslint-disable-next-line no-await-in-loop
      const ans = await inquirer.prompt([...serviceInstance.defaultPrompts, ...serviceInstance.prompts])

      const options = {
        Image: `${serviceInstance.organization}/${serviceInstance.shortName()}:${ans.tag}`,
        name: `TO--${serviceInstance.shortName()}--${ans.tag}--${ans.port}`,
        Env: [
          'FOO=bar',
          'BAZ=quux',
        ],
        Labels: {
          'com.example.vendor': 'Acme',
          'com.example.license': 'GPL',
          'com.example.version': '1.0',
        },
        HostConfig: {
          Binds: [
            `${ans.volume}:/data`,
          ],
          PortBindings: {},
          NetworkMode: 'bridge',
          Devices: [],
        },
        NetworkingConfig: {
        },
      }
      const containerPortBindingKey = `${serviceInstance.defaultPort}/tcp`
      options.HostConfig.PortBindings[containerPortBindingKey] =  [{HostPort: `${ans.port}`}]

      // check if the port is in use
      // check if the name is already taken
      // check if we have the image downloaded

      if (await this.imageIsDownloaded(serviceInstance, ans.tag)) {
        this.enableContainer(serviceInstance, options)
      } else {
        this.downloadImage(serviceInstance, ans.tag)
        .then(() => this.enableContainer(serviceInstance, options))
      }
    }

    // ask all the questions in the instance
    // set all the answers back into the service instance
    // use the service istance to download an image
    // use the service instancee to run a container
  }
}
