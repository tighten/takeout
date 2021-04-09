const spawn = require('cross-spawn')

export default class EnvironmentShell {
  static isPortAvailable(port: number): boolean {
    const result = EnvironmentShell.netstatCmd(port)
    return result.status !== 0
  }

  static netstatCmd(port: number): any {
    // @TODO: is there any danger in concatenating the command like this? Do we need to escape it somehow?
    const portText = EnvironmentShell.isLinuxOs()
      ? `:${port} `
      : `.${port} `

    const cmd = `netstat -vanp tcp | grep '${portText}' | grep -v 'TIME_WAIT' | grep -v 'CLOSE_WAIT' | grep -v 'FIN_WAIT'`

    return spawn.sync('sh', ['-c', cmd])
  }

  static isLinuxOs(): boolean {
      return process.platform === 'linux'
  }
}
