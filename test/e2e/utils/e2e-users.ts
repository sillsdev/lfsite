export const usersToCreate = [
  'admin',
  'manager',
  'member',
  'member2',
  'observer',
] as const;

export type E2EUsernames = typeof usersToCreate[number];
