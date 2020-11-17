import {execSync} from 'child_process'

export const jsonStringToArray = (json: string)/* : Array<Record<string, any>> */ => {
  return json.split(/\r?\n/).filter(Boolean).map((row: string) => JSON.parse(row))
}

export const runAndParseAsJson = (command: string)/* : Array<Record<string, any>> */ => {
  return jsonStringToArray(execSync(command).toString())
}
