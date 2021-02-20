export default abstract class BaseService {
    protected organization = 'library';

    protected imageName: string;

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

    public repoTag(tag) {
      return `${this.organization}/${this.imageName}:${tag}`
    }

    public imageString(tag) {
      let imageString = ''
      imageString += this.organization === 'library' ? '' : this.organization
      imageString += `${this.imageName}:${tag}`
      return imageString
    }

    containerName(promptAnswers: any, tag: string|number): string {
      let portTag = ''

      for (const promptQuestion in promptAnswers) {
        if (promptQuestion.includes('port')) {
          portTag += `--${promptAnswers[promptQuestion]}`
        }
      }

      return `TO--${this.imageName}--${tag}${portTag}`
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
