import {Command, flags} from '@oclif/command'
import {menuOptions} from '../helpers'
import {DockerodeContainer} from '../types'
const inquirer = require('inquirer')
const Docker = require('dockerode')

export default class Start extends Command {
  static description = 'Start a stopped container.'

  static flags = {
    help: flags.help({char: 'h'}),
    name: flags.string({char: 'n', description: 'name to print'}),
    all: flags.boolean({char: 'a'}),
  }

  static args = [{name: 'container'}]

  async startContainer(id: string) {
    try {
      const container = (new Docker()).getContainer(id)
      const containerInspection = await container.inspect()
      container.start((err: Error) => {
        if (err) throw err
        this.log(`Container ${containerInspection.Name.substring(1)} successfully started.`)
      })
    } catch (error) {
      this.error(error)
    }
  }

  async run() {
    const {args, flags} = this.parse(Start)
    if (args.container) {
      this.startContainer(args.container)
    } else {
      (new Docker()).listContainers(
        {
          all: true,
          filters: {
            name: ['TO--'],
            status: ['exited'],
          }},
        (err: any, containers: any) => {
          if (err) return this.error(err)
          if (containers.length === 0) return this.log('There are no containers to start.')

          if (flags.all) {
            containers.forEach((container: DockerodeContainer) => {
              this.startContainer(container.Id)
            })
          } else {
            inquirer.prompt([
              {
                type: 'checkbox',
                name: 'containers',
                message: 'Which container(s) would you like to start?',
                choices: menuOptions(containers),
              },
            ])
            .then((answers: any) => {
              answers.containers.forEach((answer: string) => {
                this.startContainer(answer)
              })
            })
          }
        }
      )
    }
  }
}
