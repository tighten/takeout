import {Command, flags} from '@oclif/command'
import {cli} from 'cli-ux'
import Docker from '../shell/docker'

export default class List extends Command {
  static description = 'List the Takeout-enabled containers.'

  static flags = {
    help: flags.help({char: 'h', description: 'Show CLI help'}),
    json: flags.boolean({char: 'j', description: 'Return as JSON'}),
  }

  async run() {
    const {flags} = this.parse(List)

    if (flags.json) {
      // @todo: Solve TS error "Property 'ID' does not exist on type 'Object'"
      this.log(JSON.stringify(Docker.listTakeoutContainers().map(container => {
        return {
          id: container.ID,
          name: container.Names,
        }
      })))
    } else {
      cli.table(Docker.listTakeoutContainers(), {
        ID: {
          header: 'Container ID',
        },
        Names: {
          header: 'Name',
        },
      }, {
        printLine: this.log,
        ...flags,
      })
    }
  }
}
