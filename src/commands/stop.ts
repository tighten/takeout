import {Command, flags} from '@oclif/command'
import {menuOptions} from '../helpers'
import {DockerodeContainer} from '../types'
const inquirer = require('inquirer')
const Docker = require('dockerode')

export default class Stop extends Command {
  static description = 'Stop a started container.'

  static flags = {
    help: flags.help({char: 'h'}),
    name: flags.string({char: 'n', description: 'name to print'}),
    all: flags.boolean({char: 'a'}),
  }

  static args = [{name: 'container'}]

  async stopContainer(id: string) {
    try {
      const container = (new Docker()).getContainer(id)
      const containerInspection = await container.inspect()
      container.stop((err: any, data: any) => {
        if (err) throw err
        this.log(`Container ${containerInspection.Name.substring(1)} successfully stopped.`)
      })
    } catch (error) {
      this.error(error)
    }
  }

  async run() {
    const {args, flags} = this.parse(Stop)

    if (args.container) {
      this.stopContainer(args.container)
    } else {
      (new Docker()).listContainers(
        {
          all: true,
          filters: {
            name: ['TO--'],
            status: ['running'],
          }},
        (err: any, containers: any) => {
          if (err) return this.error(err)
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
      )
    }
  }
}
