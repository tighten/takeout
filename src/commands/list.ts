import {Command, flags} from '@oclif/command'
import {cli} from 'cli-ux'
import {DockerContainer} from '../types'
const Docker = require('dockerode')

export default class List extends Command {
  static description = 'List the Takeout-enabled containers.'

  static flags = {
    help: flags.help({char: 'h', description: 'Show CLI help'}),
    json: flags.boolean({char: 'j', description: 'Return as JSON'}),
  }

  async run() {
    const {args, flags} = this.parse(List)

    const docker = new Docker()
    docker.listContainers(
      {
        all: true,
        filters: {
          name: ['TO--'],
        },
      },
      (err: any, containers: any) => {
        if (err) return this.error(err)
        if (flags.json) {
          this.log(JSON.stringify(containers))
        } else {
          const tableData = containers.map((container: DockerContainer) => ({...container, Names: container.Names[0].substring(1)}))
          cli.table(tableData, {
            Id: {
              header: 'Container ID',
            },
            Names: {
              header: 'Name',
            },
            Status: {
              header: 'Status',
            },
          }, {
            printLine: this.log,
            ...flags,
          })
        }
      }
    )
  }
}
