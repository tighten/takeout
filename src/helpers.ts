import {DockerodeContainer, Choice, ServiceChoice} from './types'
import {execSync} from 'child_process'
const fs = require('fs')

export const menuOptions = (containers: DockerodeContainer[]): Choice[] => {
  return containers.map(container => ({
    name: container.Names[0],
    value: container.Id,
  }))
}

export const jsonStringToArray = (json: string): string[] => {
  return json.split(/\r?\n/).filter(Boolean).map((row: string) => JSON.parse(row))
}

export const runAndParseAsJson = (command: string): any => {
  return jsonStringToArray(execSync(command).toString())
}

export const getAllServices = (): any => {
  const services = fs.readdirSync('./src/services')
  return services.map((service: string): ServiceChoice => ({
    category: service,
    name: service,
  }))
}

export const convertToRow = (container: DockerodeContainer): any => {
  return ({...container, Names: container.Names[0].substring(1)})
}
export default {
  jsonStringToArray,
  menuOptions,
  runAndParseAsJson,
}
