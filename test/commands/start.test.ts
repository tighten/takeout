import {expect, test} from '@oclif/test'
import Docker from '../../src/shell/docker'
import {DockerContainer} from '../../src/types'
const child_process = require('child_process')
const inquirer = require('inquirer')
const sinon = require('sinon')

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
  .stub(child_process, 'execSync', sinon.stub())
  .stub(Docker, 'validContainerId', () => true)
  .command(['start', 'existentId'])
  .it('starts a specific container', ctx => {
    expect(ctx.stdout).to.contain('Container successfully started.')
    expect(child_process.execSync.calledOnce).to.equal(true)
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
  .stub(inquirer, 'prompt', sinon.stub().returns(Promise.resolve({containers: []})))
  .command(['start'])
  .it('asks user to select a container to start if no id is provided', () => {
    expect(inquirer.prompt.called).to.equal(true)
  })

  test
  .stub(Docker, 'listTakeoutContainers', () => {
    return fakeTakeoutContainers
  })
  .stub(inquirer, 'prompt', sinon.stub().returns(Promise.resolve({containers: ['redis', 'meilisearch']})))
  .stdout()
  .stub(Docker, 'startContainer', () => null)
  .stub(Docker, 'validContainerId', () => true)
  .command(['start'])
  .it('starts the selected containers when no id is provided', ctx => {
    expect(ctx.stdout).contains('Container successfully started.\nContainer successfully started.')
  })
})
