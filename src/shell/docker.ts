import {runAndParseAsJson} from './shell'
import {DockerContainer} from '../types'
import {execSync} from 'child_process'

export default class Docker {
  static listTakeoutContainers(): DockerContainer[] {
    return runAndParseAsJson("docker ps -a --filter 'name=TO-' --format '{{ json . }}' --all")
  }

  static startContainer(id: string) {
    // @todo validate that it exists in listTakeoutContainers()
    execSync(`docker start ${id}`)
    // @todo handle errors
  }
}
