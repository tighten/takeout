import {Prompt, PromptResponse} from '../types'

export default abstract class BaseService {
  constructor(shell, environment, docker) {
    this.shell = shell
    this.environment = environment
    this.docker = docker
    this.defaultPrompts = this.defaultPrompts.map(prompt => prompt.shortname === 'port' ? {...prompt, default: this.defaultPort} : {...prompt})

    this.promptResponses = [
      {prompt: 'organization', value: this.organization},
      {prompt: 'image_name', value: this.imageName()},
    ]
  }

  protected static category: string;

  protected static displayName: string;

  protected organization = 'library'; // Official repositories use `library` as the organization name.

  protected imageName: () => string;

  // protected dockerTagsClass = DockerTags::class;

  protected abstract tag: string;

  protected abstract dockerRunTemplate: string;

  protected abstract defaultPort: number;

  protected defaultPrompts: Prompt[] = [
    {
      shortname: 'port',
      prompt: 'Which host port would you like %s to use?',
      default: 0, // Default is set in the constructor
    },
    {
      shortname: 'tag',
      prompt: 'Which tag (version) of %s would you like to use?',
      default: 'latest',
    },
  ];

  protected prompts = [];

  protected promptResponses: PromptResponse[] = [];

  protected shell;

  protected environment;

  protected docker;

  protected useDefaults = false;
}
