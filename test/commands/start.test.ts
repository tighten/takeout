import {expect, test} from '@oclif/test'

describe('start', () => {
  test
  .stderr()
  .command(['start', 'nonExistentId'])
  .it('runs start nonExistentId', ctx => {
    expect(ctx.stderr).to.contain('not a valid')
  })
})
