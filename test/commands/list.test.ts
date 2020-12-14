import {expect, test} from '@oclif/test'
import Docker from '../../src/shell/docker'
import {DockerodeContainer} from '../../src/types'

const fakeTakeoutContainers: DockerodeContainer[] = [
  {
    Id: 'redis',
    Names: ['redis'],
    Status: 'Running',
  },
]

describe('list', () => {
  test
  .stdout()
  .stub(Docker, 'listTakeoutContainers', () => {
    return fakeTakeoutContainers
  })
  .command(['list'])
  .it('displays a list of takeout containers', ctx => {
    expect(ctx.stdout).to.contain('redisId')
  })

  test
  .stdout()
  .stub(Docker, 'listTakeoutContainers', () => {
    return fakeTakeoutContainers
  })
  .command(['list', '--json'])
  .it('displays the list in json format', ctx => {
    expect(ctx.stdout).to.contain('"id":"redisId"')
  })
})
