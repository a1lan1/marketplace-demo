export const Role = {
  ADMIN: 'admin',
  MANAGER: 'manager',
  SELLER: 'seller',
  BUYER: 'buyer',
  USER: 'user'
} as const

export type Role = (typeof Role)[keyof typeof Role];
