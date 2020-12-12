import {Command, flags} from '@oclif/command'
import inquirer = require('inquirer')
import Docker from '../shell/docker'
import {getAllServices} from '../helpers'
import Mysql from '../services/mysql'
import BaseService from '../services/base-service'

export default class Enable extends Command {
  static description = 'Enable services.'

  static strict = false // for variable length arguments

  static flags = {
    help: flags.help({char: 'h'}),
    force: flags.boolean({char: 'f'}),
  }

  enableService(shortname: string) {
    try {
      Docker.enableService(shortname)
      this.log('Container successfully started.')
    } catch (error) {
      this.error(error)
    }
  }

  async run() {
    const {argv} = this.parse(Enable)
    this.log(argv)
    if (argv.length === 0) {
      this.log('no args')
    }
    // this.log(JSON.stringify(BaseService.category))
    // this.log(getAllServices())
    // const {args} = this.parse(Enable)

    // if (args.service) {
    //   this.log(args.service)
    // } else {
    //   inquirer.prompt([
    //     {
    //       type: 'checkbox',
    //       name: 'services',
    //       message: 'Which service do you want to enable?',
    //       choices: [{name: 'mysql', value: 'mysql'}, {name: 'meilisearch', value: 'meilisearch'}],
    //     },
    //   ])
    //   .then((answers: any) => {
    //     this.log(answers)
    //     answers.services.forEach((answer: string) => {
    //       this.enableService(answer)
    //     })
    //   })
    // }
  }
}
