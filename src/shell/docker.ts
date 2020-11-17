import {runAndParseAsJson} from './shell'
import {Container} from '../misc/interfaces'

export default class Docker {
  static listTakeoutContainers(): Array<Container> {
    return runAndParseAsJson("docker ps -a --filter 'name=TO-' --format '{{ json . }}' --all")
  }
}
