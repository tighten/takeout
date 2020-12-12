import BaseService from './base-service'

export default class Mysql extends BaseService {
  protected static category = 'Database'

  protected static displayName = 'MySQL'

  protected imageName = () => 'mysql'

  protected defaultPort = 3306

  protected $prompts = [
    {
      shortname: 'volume',
      prompt: 'What is the Docker volume name?',
      default: 'mysql_data',
    },
    {
      shortname: 'root_password',
      prompt: 'What will the root password be? (null by default)',
      default: '',
    },
  ];
}
