import BaseService from './base-service'
import EnvironmentShell from '../shell/environmentshell'

export default class MySQL extends BaseService {
  static category  = 'Database'

  public organization  = 'library'

  public imageName  = 'mysql'

  _defaultPort = 3306;

  environmentVariables(promptAnswers: any): string[] {
    const environment: string[] = []
    if (promptAnswers.root_password === '') {
      environment.push('MYSQL_ALLOW_EMPTY_PASSWORD=true')
      environment.push('MYSQL_ROOT_PASSWORD=')
    }
    return environment
  }

  prompts = [
    {
      type: 'input',
      name: 'port',
      message: this.defaultPortMessage(),
      default: this.defaultPort,
      validate: function (input: string|number) {
        const portCheck = ((res: any, rej: any) => {
          const a = EnvironmentShell.netstatCmd()
          console.log('a: ', a)
          if (parseInt(input, 10) === 3306) {
            rej('Some error')
          }
          res(true)
        })
        return new Promise(portCheck)
      },
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
