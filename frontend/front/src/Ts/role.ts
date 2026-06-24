export enum Role {
  Guest = 'guest',
  Admin = 'admin',
  Developer = 'developer',
  Member = 'member',
}

const roleLabels: Record<Role, string> = {
  [Role.Guest]: 'Гость',
  [Role.Admin]: 'Администратор',
  [Role.Developer]: 'Разработчик',
  [Role.Member]: 'Участник',
}

export function getRoleLabel(role: string): string {
  return roleLabels[role as Role] ?? role
}
