import Command from '../commands/base'
import {flags} from '@oclif/command'
import {menuOptions} from '../helpers'
import {DockerodeContainer} from '../types'
import inquirer = require('inquirer')
import dockerBaseMixin from '../mixins/docker-base'

export default class Stop extends dockerBaseMixin(Command) {
  static description = 'Stop a started container.'

  /** Allow multiple services (argv) to be stopped at once */
  static strict = false

  static flags = {
    help: flags.help({char: 'h'}),
    name: flags.string({char: 'n', description: 'name to print'}),
    all: flags.boolean({char: 'a'}),
  }

  async run() {
    const {argv, flags} = this.parse(Stop)

    this.initializeCommand()

    if (flags.all) {
      const runningTOContainers = await this.listTakeoutContainers(['running'])

      if (runningTOContainers.length === 0) {
        throw new Error('No containers to stop.')
      }

      runningTOContainers.forEach((container: any) => {
        this.stopContainer(container.Id)
      })
    } else if (argv?.length) {
      argv.forEach(async (arg: string) => {
        const containers = await this.takeoutContainersByShortNames([arg], ['running'])
        if (containers.length === 0) {
          // @TODO: Handle error more gracefully
          throw new Error(`No containers found for ${arg}`)
        } else if (containers.length > 1) {
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
        } else {
          this.stopContainer(containers[0].Id)
        }
      })
    } else {
      const containers = await this.listTakeoutContainers(['running'])

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
