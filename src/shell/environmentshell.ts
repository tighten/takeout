const spawn = require('cross-spawn')

export default class EnvironmentShell {
  static netstatCmd(): string {
    return spawn.sync("netstat -vanp tcp | grep '3306' | grep -v 'TIME_WAIT' | grep -v 'CLOSE_WAIT' | grep -v 'FIN_WAIT'")
  }
}
