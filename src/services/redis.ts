import EnvironmentShell from '../shell/environmentshell';
import BaseService from './base-service'

export default class Redis extends BaseService {
  static category  = 'Cache'

  public organization  = 'library'

  public imageName  = 'redis'

  _defaultPort = 6379;

  environmentVariables(promptAnswers: any): string[] {
    const environment: string[] = []

    return environment
  }

  prompts = [
    {
      type: 'input',
      name: 'volume',
      message: 'What is the Docker volume name?',
      default: 'redis_data',
    },
    {
      type: 'input',
      name: 'port',
      message: this.defaultPortMessage(),
      default: this.defaultPort,
      validate: function (port: number) {
        return new Promise((resolve, reject) => {
          if (! EnvironmentShell.isPortAvailable(port)) {
            reject(`Port ${port} has already been taken. Please, try another one.`)
            return;
          }

          resolve(true);
        })
      },
    },
  ];
}
