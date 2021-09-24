import {
  DockerodeContainer,
  Choice,
  ServiceChoice,
  ContainerTableRow,
} from './types'
import {execSync} from 'child_process'
import Services  from './services'
import fs = require('fs')

export const menuOptions = (containers: DockerodeContainer[]): Choice[] => {
  return containers.map(container => ({
    name: container.Names[0],
    value: container.Id,
  }))
}

export const volMenuOptions = (volumes: any[]): Choice[] => {
  return volumes.map(volume => ({
    name: volume.Name,
    value: volume,
  }))
}

export const jsonStringToArray = (json: string): string[] => {
  return json.split(/\r?\n/).filter(Boolean).map((row: string) => JSON.parse(row))
}

export const runAndParseAsJson = (command: string): any => {
  return jsonStringToArray(execSync(command).toString())
}

export const convertToRow = (container: DockerodeContainer): ContainerTableRow => {
  return ({
    ...container,
    Id: container.Id.substring(0, 12), // Truncate the Id string to 12 chars
    Name: container.Names[0].substring(1), // Grab the first name in Names array
  })
}

export const convertVolToRow = (volume: any): any => {
  return ({
    ...volume,
    Name: volume.Name,
    Mountpoint: volume.Mountpoint,
  })
}

export const containersToTable = (containers: DockerodeContainer[]): ContainerTableRow[] => {
  return containers.map((container: DockerodeContainer) => convertToRow(container))
}

export const volumesToTable = (volumes: any[]): any[] => {
  return volumes.map((volume: any) => convertVolToRow(volume))
}

export const availableServices = Object.entries(Services).map(([key, Service]): ServiceChoice => ({
  category: Service.category,
  name: Service.name,
  value: key.toLowerCase(),
}))

export const serviceByShortName = (shortName: string) => {
  return Object.values(Services).find(Service => {
    return Service.name.toLowerCase() === shortName
  })
}

export default {
  jsonStringToArray,
  menuOptions,
  volMenuOptions,
  runAndParseAsJson,
  convertToRow,
  containersToTable,
  volumesToTable,
}
