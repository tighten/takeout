import {expect, test} from '@oclif/test'
const inquirer = require('inquirer')
const sinon = require('sinon')
const child_process = require('child_process')

describe('enable', () => {
  test
  .stub(inquirer, 'prompt', sinon.stub().returns(Promise.resolve({services: ['mysql']})))
  .stub(child_process, 'execSync', sinon.stub())
  .command(['enable'])
  .it('displays a list of services to enabled', () => {
    expect(child_process.execSync.calledOnce).to.equal(true)
  })
})
