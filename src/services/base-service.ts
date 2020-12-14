export default abstract class BaseService {
    protected abstract imageName: string;

    protected tagMessage() {
      return `which tag (version) of ${this.imageName} would you like to use?`
    }

    protected defaultPortMessage() {
      return `Which host port would you like ${this.imageName} to use?`
    }

    protected defaultPrompts = [
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

