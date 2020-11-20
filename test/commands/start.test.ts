import {expect, test} from '@oclif/test'
import Docker from '../../src/shell/docker'

describe('start', () => {
  test
  .stderr()
  .command(['start', 'nonExistentId'])
  .catch(error => {
    expect(error.message).matches(/nonExistentId is not a valid container ID/)
  })
  .it('should error when a non existend container id is entered.')

  test
  .stdout()
  .stub(Docker, 'startContainer', () => null)
  .stub(Docker, 'validContainerId', () => true)
  .command(['start', 'existentId'])
  .it('start up a container and exits successfully', ctx => {
    expect(ctx.stdout).to.contain('Container successfully started.')
  })

  test
  .stdout()
  .command(['start', '--help'])
  .exit(0)
  .it('shows a help output', ctx => {
    expect(ctx.stdout).to.contain('takeout start')
  })
})
