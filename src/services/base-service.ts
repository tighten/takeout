export default abstract class BaseService {
    abstract imageName: string;

    abstract organization: string;

    abstract defaultPort(): number;

    protected tagMessage() {
      return `Which tag (version) of ${this.imageName} would you like to use?`
    }

    defaultPortMessage() {
      return `Which host port would you like ${this.imageName} to use?`
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
        message: () => this.tagMessage(),
        default: 'latest',
      },
      {
        type: 'input',
        name: 'port',
        message: () => this.defaultPortMessage(),
        default: () => this.defaultPort(),
      },
    ]
}
