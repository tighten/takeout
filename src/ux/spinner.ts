import cli from 'cli-ux'
const spinner = cli.action

// @ts-ignore
spinner.frames = ['⣾', '⣷', '⣯', '⣟', '⡿', '⢿', '⣻', '⣽']
export default spinner
