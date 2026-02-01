export interface CardDetails {
  number: string;
  name: string;
  expiry: string;
  cvv: string;
}

export interface PaymentMethod {
  id: string;
  type: string;
  brand: string;
  last_four: string;
  expires_at: string;
  is_default: boolean;
  provider: string;
  provider_id: string;
}

export interface PaymentForm {
  amount: number;
  currency: string;
  payment_method_id: string;
  save_card: boolean;
  provider: string;
}

export enum PaymentProvider {
  Stripe = 'stripe',
  Custom = 'custom',
  Fake = 'fake',
}

export type PaymentSelection = 'new' | 'balance';

export type PaymentMethodType = 'balance' | 'card';
