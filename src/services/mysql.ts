import BaseService from './base-service'

export default class MySQL extends BaseService {
  protected organization  = 'library'

  protected imageName = 'mysql'

  protected defaultPort = () => 3306;

  protected prompts = [
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

