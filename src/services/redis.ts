import BaseService from './base-service'

export default class Redis extends BaseService {
  static category  = 'Cache'

  static organization  = 'library'

  _defaultPort = 6739;

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
    },
  ];
}
