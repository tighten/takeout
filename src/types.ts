export interface DockerContainer {
  ID: string;
  Names: string;
  Status: string;
}

export interface Choice {
  name: string;
  value: string;
}

export interface Service {
   category: string;
   displayName: string;
   organization: string;
   imageName: string;
   dockerTagsClass: string;
   tag: string;
   dockerRunTemplate: string;
   defaultPort: number;
}

export interface ServiceChoice {
  category: string;
  name: string;
}

export interface Prompt {
  shortname: string;
  prompt: string;
  default: string|number;
}

export interface PromptResponse {
  prompt: string;
  value: string|number;
}
