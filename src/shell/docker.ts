const { execSync } = require('child_process');

export default class Docker {
  static listTakeoutContainers() {
    const output = execSync("docker ps -a --filter 'name=TO-' --format 'table {{.ID}}|{{.Names}}|{{.Status}}|{{.Ports}}|{{.Label \"com.tighten.takeout.Base_Alias\"}}|{{.Label \"com.tighten.takeout.Full_Alias\"}}'")
    return this.dockerTableToArray(output.toString())
  }

  static dockerTableToArray(table) {
    // @todo make this actually work
    return [
      {'Container Id': '123', Name: 'Best'},
      {'Container Id': '456', Name: 'Better'},
    ]
  }
}
