import Command from '@oclif/command'
import chalk = require('chalk')

export default abstract class extends Command {
    private consoleColors = {
      error: chalk.bold.bgRed.white,
      success: chalk.bold.green,
    }

    public logError(message: string) {
      console.log(this.consoleColors.error(message))
    }

    public logSuccess(message: string) {
      console.log(this.consoleColors.success(message))
    }

    async catch(err: Error) {
      this.logError(err.message)
    }
}
