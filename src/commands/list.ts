import {Command, flags} from '@oclif/command'
import {cli} from 'cli-ux'
import Docker from '../shell/docker'
import {Container} from '../misc/interfaces'

export default class List extends Command {
  static description = 'List the Takeout-enabled containers.'

  static flags = {
    help: flags.help({char: 'h', description: 'Show CLI help'}),
    json: flags.boolean({char: 'j', description: 'Return as JSON'}),
  }

  listAsJson() {
    this.log(JSON.stringify(Docker.listTakeoutContainers().map((container: Container) => {
      return {
        id: container.ID,
        name: container.Names,
      }
    })))
  }

  listAsTable() {
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

  async run() {
    const {flags} = this.parse(List)

    flags.json ? this.listAsJson() : this.listAsTable()
  }
}
