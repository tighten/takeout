import {expect, test} from '@oclif/test'
import Docker from '../../src/shell/docker'
import {DockerContainer} from '../../src/types'

const fakeTakeoutContainers: DockerContainer[] = [
  {
    ID: 'redisId',
    Names: 'redis',
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
  .it('runs list --json', ctx => {
    expect(ctx.stdout).to.contain('"id":"redisId"')
  })
})
