export default abstract class BaseService {
    static organization = 'library';

    static category: string;

    abstract _defaultPort: number;

    set defaultPort(port) {
      this._defaultPort = port
    }

    get defaultPort() {
      return this._defaultPort
    }

    protected tagMessage() {
      return `Which tag (version) of ${this.shortName()} would you like to use?`
    }

    defaultPortMessage() {
      return `Which host port would you like ${this.shortName()} to use?`
    }

    shortName() {
      return this.constructor.name.toLowerCase()
    }

    containerName(promptAnswers: any, tag: string|number): string {
      let portTag = ''

      for (const promptQuestion in promptAnswers) {
        if (promptQuestion.includes('port')) {
          portTag += `--${promptAnswers[promptQuestion]}`
        }
      }

      return `TO--${this.shortName()}--${tag}${portTag}`
    }

    defaultPrompts = [
      {
        type: 'input',
        name: 'tag',
        message: this.tagMessage(),
        default: 'latest',
      },
    ]
}
