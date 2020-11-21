import {DockerContainer, Choice} from '../types'

const menuOption: {
  (container: DockerContainer): Choice;
} = container => {
  return {
    name: container.Names,
    value: container.ID,
  }
}

const menuOptions: {
  (containers: DockerContainer[]): Choice[];
} = containers => {
  return containers.map(container => menuOption(container))
}

const Transforms = {
  menuOption,
  menuOptions,
}

export default Transforms
