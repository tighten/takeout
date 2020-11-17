import {runAndParseAsJson} from './shell'

export default class Docker {
  static listTakeoutContainers(): Array<Container> {
    return runAndParseAsJson("docker ps -a --filter 'name=TO-' --format '{{ json . }}' --all")
  }
}
