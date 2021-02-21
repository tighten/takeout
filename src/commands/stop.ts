import Command from '../commands/base'
import {flags} from '@oclif/command'
import {menuOptions} from '../helpers'
import {DockerodeContainer} from '../types'
import inquirer = require('inquirer')
import dockerBaseMixin from '../mixins/docker-base'

export default class Stop extends dockerBaseMixin(Command) {
  static description = 'Stop a started container.'

  static flags = {
    help: flags.help({char: 'h'}),
    name: flags.string({char: 'n', description: 'name to print'}),
    all: flags.boolean({char: 'a'}),
  }

  static args = [{name: 'container'}]

  async run() {
    const {args, flags} = this.parse(Stop)

    this.initializeCommand()

    if (args.container) {
      // @TODO translate short name to container id
      // @TODO menu selection if multiple containers
      // @TODO stop all with an --all flag
      this.stopContainer(args.container)
    } else {
      const containers = await this.listTakeoutContainers()

      if (containers.length === 0) return this.log('There are no containers to stop.')

      if (flags.all) {
        containers.forEach((container: DockerodeContainer) => {
          this.stopContainer(container.Id)
        })
      } else {
        inquirer.prompt([
          {
            type: 'checkbox',
            name: 'containers',
            message: 'Which container(s) would you like to stop?',
            choices: menuOptions(containers),
          },
        ])
        .then((answers: any) => {
          answers.containers.forEach((answer: string) => {
            this.stopContainer(answer)
          })
        })
      }
    }
  }
}
