import BaseService from './base-service'

export default class MySQL extends BaseService {
  static category  = 'Database'

  static organization  = 'library'

  _defaultPort = 3306;

  environmentVariables(promptAnswers: any): string[] {
    const environment: string[] = []
    if (promptAnswers.root_password === '') {
      environment.push('MYSQL_ALLOW_EMPTY_PASSWORD=true')
    }
    return environment
  }

  prompts = [
    {
      type: 'input',
      name: 'port',
      message: this.defaultPortMessage(),
      default: this.defaultPort,
    },
    {
      type: 'input',
      name: 'volume',
      message: 'What is the Docker volume name?',
      default: 'mysql_data',
    },
    {
      type: 'input',
      name: 'root_password',
      message: 'What will the root password be? (null by default)',
      default: '',
    },
  ];
}
