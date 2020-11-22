import {DockerContainer, Choice} from './types'
import {execSync} from 'child_process'

export const menuOptions = (containers: DockerContainer[]): Choice[] => {
  return containers.map(container => ({
    name: container.Names,
    value: container.ID,
  }))
}

export const jsonStringToArray = (json: string): string[] => {
  return json.split(/\r?\n/).filter(Boolean).map((row: string) => JSON.parse(row))
}

export const runAndParseAsJson = (command: string): any => {
  return jsonStringToArray(execSync(command).toString())
}

export default {
  jsonStringToArray,
  menuOptions,
  runAndParseAsJson,
}
