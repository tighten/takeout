import {DockerContainer, Choice, ServiceChoice} from './types'
import {execSync} from 'child_process'
const fs = require('fs')

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

export const getAllServices = (): any => {
  const services = fs.readdirSync('./src/services')
  return services.map((service: string): ServiceChoice => ({
    category: service,
    name: service,
  }))
}

export default {
  jsonStringToArray,
  menuOptions,
  runAndParseAsJson,
}
