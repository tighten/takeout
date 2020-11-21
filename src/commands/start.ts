import {Command, flags} from '@oclif/command'
import Docker from '../shell/docker'
import Transforms from '../helpers/transform'
const inquirer = require('inquirer')

export default class Start extends Command {
  static description = 'Start a stopped container.'

  static flags = {
    help: flags.help({char: 'h'}),
  }

  static args = [{name: 'container'}]

  startContainer(id: string) {
    try {
      Docker.startContainer(id)
      this.log('Container successfully started.')
    } catch (error) {
      this.error(error)
    }
  }

  async run() {
    const {args} = this.parse(Start)

    if (args.container) {
      this.startContainer(args.container)
    } else {
      inquirer.prompt([
        {
          type: 'checkbox',
          name: 'containers',
          message: 'Which container(s) would you like to start?',
          choices: Transforms.menuOptions(Docker.stoppedTakeoutContainers()),
        },
      ])
      .then((answers: any) => {
        answers.containers.forEach((answer: string) => {
          this.startContainer(answer)
        })
      })
    }
  }
}
