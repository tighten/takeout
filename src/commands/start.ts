import {Command, flags} from '@oclif/command'
import Docker from '../shell/docker'

export default class Start extends Command {
  static description = 'Start a stopped container'

  static flags = {
    help: flags.help({char: 'h'}),
  }

  static args = [{name: 'container'}]

  startContainer(id: string) {
    Docker.startContainer(id)
    this.log('Container stopped.')
  }

  async run() {
    const {args} = this.parse(Start)

    if (args.container) {
      this.startContainer(args.container)
    } else {
      this.error('Sorry, no list built yet.')
    }
  }
}
