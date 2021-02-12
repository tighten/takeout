import {Command, flags} from '@oclif/command'
import inquirer = require('inquirer')
import {availableServices, serviceByShortName} from '../helpers'

export default class Enable extends Command {
  static description = 'Enable services via Takeout'

  /** Allow multiple services to be enabled at once */
  static strict = false

  static flags = {
    help: flags.help({char: 'h'}),
  }

  async run() {
    const {argv} = this.parse(Enable)
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
      console.log(ans)
    }

    // ask all the questions in the instance
    // set all the answers back into the service instance
    // use the service istance to download an image
    // use the service instancee to run a container
  }
}
