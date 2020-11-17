import {expect, test} from '@oclif/test'

describe('list', () => {
  test
  .stdout()
  .command(['list'])
  .it('runs list', ctx => {
    // @todo how do we run a functional/useful assertion? Can we mock shell or something?
    expect(ctx.stdout).to.contain('Container ID')
  })

  test
  .stdout()
  .command(['list', '--json'])
  .it('runs list --json', ctx => {
    // @todo How do we make this actually functional? Maybe parse it as JSON and then check that it didn't error?
    expect(ctx.stdout).to.contain('[')
  })
})
