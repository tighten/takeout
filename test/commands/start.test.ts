import {expect, test} from '@oclif/test'
import Docker from '../../src/shell/docker'
const inquirer = require('inquirer')
import {DockerContainer} from '../../src/types'

const fakeTakeoutContainers: DockerContainer[] = [
  {
    ID: 'redisId',
    Names: 'redis',
    Status: 'Exited 0 minutes ago.',
  },
]

describe('start', () => {
  test
  .stderr()
  .command(['start', 'nonExistentId'])
  .catch(error => {
    expect(error.message).matches(/nonExistentId is not a valid container ID/)
  })
  .it('should error when a non existent container id is entered.')

  test
  .stdout()
  .stub(Docker, 'startContainer', () => null)
  .stub(Docker, 'validContainerId', () => true)
  .command(['start', 'existentId'])
  .it('starts a specific container', ctx => {
    expect(ctx.stdout).to.contain('Container successfully started.')
  })

  test
  .stdout()
  .command(['start', '--help'])
  .exit(0)
  .it('shows a help output', ctx => {
    expect(ctx.stdout).to.contain('takeout start')
  })

  test
  .stub(Docker, 'listTakeoutContainers', () => {
    return fakeTakeoutContainers
  })
  .stub(inquirer, 'prompt', () => Promise.resolve({containers: ['redis']}))
  .stub(Docker, 'validContainerId', () => true)
  .stub(Docker, 'startContainer', () => true)
  .stdout()
  .command(['start'])
  .it('run start without arguments', async ctx => {
    expect(ctx.stdout).to.contain('Container successfully started.')
  })
})
