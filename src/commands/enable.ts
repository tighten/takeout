import {Command, flags} from '@oclif/command'
const Docker = require('dockerode')
import MySQL from '../services/mysql'
import inquirer = require('inquirer');

export default class Enable extends Command {
  static description = 'describe the command here'

  static flags = {
    help: flags.help({char: 'h'}),
    // flag with a value (-n, --name=VALUE)
    name: flags.string({char: 'n', description: 'name to print'}),
    // flag with no value (-f, --force)
    force: flags.boolean({char: 'f'}),
  }

  static args = [{name: 'file'}]

  async run() {
    // ask the required questions
    // set the values
    // create the container with options
    // start the container
    // tell the user the container was started
    const {args, flags} = this.parse(Enable)
    const service = new MySQL()
    inquirer.prompt([...service.defaultPrompts, ...service.prompts])
    .then(answers => {
      let auxContainer
      const docker = new Docker()
      docker.createContainer({
        Image: 'library/mysql:8',
        name: 'TO--mysql',
        Env: ['MYSQL_ALLOW_EMPTY_PASSWORD=true'],
        AttachStdin: true,
        AttachStdout: true,
        AttachStderr: true,
        Tty: true,
        ExposedPorts: {
          '3306/tcp': { },
        },
        PortBindings: {
          '3306/tcp': [
            {
              HostPort: '3306',
            },
          ],
        },
        OpenStdin: false,
        StdinOnce: false,
      }).then((container: any) => {
        auxContainer = container
        return auxContainer.start()
      })
    })
  }
}
