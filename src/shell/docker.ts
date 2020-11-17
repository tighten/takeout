const { execSync } = require('child_process')

export default class Docker {
  static listTakeoutContainers(): Array<Record<string, any>> {
    const output = execSync("docker ps -a --filter 'name=TO-' --format '{{ json . }}' --all")
    return this.dockerJsonToArrayOfObjects(output.toString())
  }

  static dockerJsonToArrayOfObjects(table: string) {
    return table.split(/\r?\n/).filter(Boolean).map((row: string) => JSON.parse(row))
  }
}
