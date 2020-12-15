import {Command, flags} from '@oclif/command'
const Docker = require('dockerode')
import MySQL from '../services/mysql'
import Tags from '../docker/tags/dockerhub'
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
    // @todo check for ports in use
    // @todo check for volumes in use
    .then(async answers => {
      const tag = await (new Tags(service)).resolveTag(answers.tag)

      let auxContainer
      const docker = new Docker()
      docker.createContainer({
        Image: `${service.organization}/${service.imageName}:${tag}`,
        name: service.containerName(answers, tag),
        // @todo add alias
        Env: service.environmentVariables(answers),
        AttachStdin: true,
        AttachStdout: true,
        AttachStderr: true,
        Tty: true,
        PortBindings: {
          [`${service.defaultPort()}/tcp`]: [
            {
              HostPort: `${answers.port}`,
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
