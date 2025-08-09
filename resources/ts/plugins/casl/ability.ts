import { createMongoAbility } from "@casl/ability";

export type Actions = "create" | "read" | "update" | "delete" | "manage";

// Allow any subject key including domain-specific ones like 'montaj'
export type Subjects = string;

export interface Rule {
  action: Actions;
  subject: Subjects;
}

export const ability = createMongoAbility<[Actions, Subjects]>();
