import Command from '../commands/base'
import {flags} from '@oclif/command'
import {menuOptions} from '../helpers'
import {DockerodeContainer} from '../types'
import inquirer = require('inquirer')
import dockerBaseMixin from '../mixins/docker-base'

export default class Disable extends dockerBaseMixin(Command) {
  static description = 'Disable an enabled container.'

  static flags = {
    help: flags.help({char: 'h'}),
    name: flags.string({char: 'n', description: 'name to print'}),
    force: flags.boolean({char: 'f'}),
  }

  static args = [{name: 'container'}]

  async run() {
    const {args, flags} = this.parse(Disable)

    this.initializeCommand()

    if (args.container) {
      // @TODO menu selection if multiple containers
      // @TODO stop all with an --all flag
      const containerIds = await this.takeoutContainerIdsByShortNames([args.container])
      let containerId = ''

      if (containerIds.length === 0) {
        throw new Error(`No containers found for ${args.container}`)
      } else if (containerIds.length > 1) {
        // @TODO: Menu choice, return containerId
      } else {
        containerId = containerIds[0]
      }

      this.disableContainer(containerId)
    } else {
      const containers = await this.listTakeoutContainers([])

      if (containers.length === 0) return this.log('There are no containers to disable.')

      if (flags.all) {
        containers.forEach((container: DockerodeContainer) => {
          this.disableContainer(container.Id)
        })
      } else {
        inquirer.prompt([
          {
            type: 'checkbox',
            name: 'containers',
            message: 'Which container(s) would you like to disable?',
            choices: menuOptions(containers),
          },
        ])
        .then((answers: any) => {
          answers.containers.forEach((answer: string) => {
            this.disableContainer(answer)
          })
        })
      }
    }
  }
}
