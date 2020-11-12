// https://www.twilio.com/blog/how-to-build-a-cli-with-node-js
import arg from 'arg';

function parseArguments(rawArgs) {
  const args = arg(
  {
    '--default': Boolean,
  },
  {
    argv: rawArgs.slice(2)
  });

  return {
    useDefaults: args['--default'] || false,
    command: args._[0],
  };
}

export function cli(args) {
  let options = parseArguments(args);
  console.log(options);
}
