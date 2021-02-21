import {Command, flags} from '@oclif/command'
import dockerBaseMixin from '../mixins/docker-base'

export default class Disable extends dockerBaseMixin(Command) {
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
    const {args, flags} = this.parse(Disable)

    const name = flags.name ?? 'world'
    this.log(`hello ${name} from /Users/jose/Code/Tighten/takeout/src/commands/disable.ts`)
    if (args.file && flags.force) {
      this.log(`you input --force and --file: ${args.file}`)
    }
  }
}
