const spawn = require('cross-spawn')

export default class DockerShell {
  static isInstalled(): boolean {
    const result = spawn.sync('docker', ['--version', '2>&1'])
    return result.status === 0
  }

  static isNotInstalled(): boolean {
    return !this.isInstalled()
  }

  static isDockerServiceRunning(): boolean {
    const result = spawn.sync('docker', ['info'])
    return result.status === 0
  }
}
