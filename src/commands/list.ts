import {Command, flags} from '@oclif/command'
import {cli} from 'cli-ux'
import Docker from '../shell/docker'

export default class List extends Command {
  static description = 'List the Takeout-enabled containers.'

  static flags = {
    help: flags.help({char: 'h'}),
    // flag with a value (-n, --name=VALUE)
    // name: flags.string({char: 'n', description: 'name to print'}),
    // flag with no value (-f, --force)
    // force: flags.boolean({char: 'f'}),
  }

  static args = [{name: 'file'}]

  async run() {
    const {args, flags} = this.parse(List)

    cli.table(Docker.listTakeoutContainers(), {
      'Container Id': {
        header: 'Container ID',
      },
      Name: {
        minWidth: 7,
      },
    }, {
      printLine: this.log,
      ...flags, // parsed flags
    })

    // const name = flags.name ?? 'world'
    // this.log(`hello ${name} from /Users/mattstauffer/Sites/node-takeout/src/commands/list.ts`)
    // if (args.file && flags.force) {
    //   this.log(`you input --force and --file: ${args.file}`)
    // }
  }
}
