import Command from '../commands/base'
import {flags} from '@oclif/command'
import {volMenuOptions, volumesToTable} from '../helpers'
import {cli} from 'cli-ux'
import inquirer = require('inquirer')
import dockerBaseMixin from '../mixins/docker-base'

export default class Volume extends dockerBaseMixin(Command) {
  static description = 'Manage data volumes.'

  static flags = {
    help: flags.help({char: 'h'}),
  }

  static args = [{name: 'command'}]

  async run() {
    const {args, flags} = this.parse(Volume)
    this.initializeCommand()
    const volumes = await this.listVolumes()
    switch (args.command) {
    case 'rm':
      inquirer.prompt([
        {
          type: 'checkbox',
          name: 'volumes',
          message: 'Which volume(s) would you like to remove?',
          choices: volMenuOptions(volumes.Volumes),
        },
      ])
      .then((answers: any) => {
        answers.volumes.forEach((volume:  any) => {
          this.removeVolume(volume)
        })
      })
      break
    case 'ls':
    default:
      cli.table(volumesToTable(volumes.Volumes), {
        Name: {header: 'Name'},
        Mountpoint: {header: 'Mountpoint'},
      }, {printLine: this.log, ...flags})
      break
    }
  }
}
