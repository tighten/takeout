import {DockerodeContainer, Choice, ServiceChoice, ContainerTableRow} from './types'
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

export const convertToRow = (container: DockerodeContainer): ContainerTableRow => {
  return ({
    ...container,
    Id: container.Id.substring(0, 12), // Truncate the Id string to 12 chars
    Name: container.Names[0].substring(1), // Grab the first name in Names array
  })
}

export const containersToTable = (containers: DockerodeContainer[]): ContainerTableRow[] => {
  return containers.map((container: DockerodeContainer) => convertToRow(container))
}

export default {
  jsonStringToArray,
  menuOptions,
  runAndParseAsJson,
  convertToRow,
  containersToTable,
}
