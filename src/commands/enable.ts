import Command from '../commands/base'
import {flags} from '@oclif/command'
import inquirer = require('inquirer')
import {availableServices, serviceByShortName} from '../helpers'
import dockerBaseMixin from '../mixins/docker-base'
import EnvironmentShell from '../shell/environmentshell'

export default class Enable extends dockerBaseMixin(Command) {
  static description = 'Enable services via Takeout'

  /** Allow multiple services (argv) to be enabled at once */
  static strict = false

  static flags = {
    help: flags.help({char: 'h'}),
  }

  async uniqueContainerName(name: string, num = 0): Promise<string> {
    if (await this.takeoutContainerByName(name)) {
      const incrementor = num + 1
      let newName
      if (name.endsWith(`--${num}`)) {
        newName = name.replace(new RegExp(`--${num}`), `--${incrementor}`)
      } else {
        newName = `${name}--${incrementor}`
      }

      return this.uniqueContainerName(newName, incrementor)
    }

    return name
  }

  async run() {
    // console.log(EnvironmentShell.netstatCmd())
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

    // conver the string to a class 'mysql' => MySQL
    const selectedServiceClasses = selectedServices.map((service: string) => {
      return serviceByShortName(service)
    })

    for (const Service of selectedServiceClasses) {
      const serviceInstance = new Service()

      // eslint-disable-next-line no-await-in-loop
      const ans = await inquirer.prompt([...serviceInstance.defaultPrompts, ...serviceInstance.prompts])
      const envs = serviceInstance.environmentVariables(ans)
      const options: any = {
        Image: `${serviceInstance.organization}/${serviceInstance.shortName()}:${ans.tag}`,
        name: `TO--${serviceInstance.shortName()}--${ans.tag}--${ans.port}`,
        Env: envs,
        Labels: {
          'com.tighten.takeout.shortname': `${serviceInstance.shortName()}`,
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

      options.HostConfig.PortBindings[containerPortBindingKey] =  [
        {
          HostPort: `${ans.port}`,
        },
      ]

      // @TODO check if the port is in use

      this.imageIsDownloaded(serviceInstance, ans.tag).then(async imageAvailable => {
        if (!imageAvailable) {
          await this.downloadImage(serviceInstance, ans.tag)
        }

        const newName = await this.uniqueContainerName(options.name)
        const newOptions = {...options, name: newName}
        this.enableContainer(serviceInstance, newOptions)
      })
    }
  }
}
