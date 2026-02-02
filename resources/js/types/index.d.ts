import type { OrderStatus } from '@/enums/OrderStatus'
import { InertiaLinkProps } from '@inertiajs/vue3'
import type { LucideIcon } from 'lucide-vue-next'

export interface Auth {
  user: User;
  roles: string[];
  permissions: string[];
}

export interface BreadcrumbItem {
  title: string;
  href: string;
}

export interface NavItem {
  title: string;
  href: NonNullable<InertiaLinkProps['href']>;
  icon?: LucideIcon;
  isActive?: boolean;
}

export type AppPageProps<
  T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
  name: string;
  quote: { message: string; author: string };
  auth: Auth;
  sidebarOpen: boolean;
  flash: FlashMessage;
};

export interface MoneyData {
  amount: number | string;
  currency: string;
  formatted: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string;
  email_verified_at: string | null;
  balance: MoneyData;
  created_at: string;
  updated_at: string;
  seller?: User;
}

export interface Seller extends User {
  average_rating: number;
  reviews_count: number;
  products?: Product[];
}

export interface Product {
  id: number;
  user_id: number;
  name: string;
  description: string | null;
  price: MoneyData;
  stock: number;
  cover_image: string;
  created_at: string;
  updated_at: string;
  seller?: User;
}

export interface ProductInOrder extends Product {
  quantity: number;
  purchase_price: MoneyData;
  line_total: MoneyData;
}

export interface ProductFormData {
  name: string;
  description: string | null;
  price: number;
  stock: number;
  cover_image: File | null;
}

export interface Order {
  id: number;
  user_id: number;
  total_amount: MoneyData;
  status: OrderStatus;
  created_at: string;
  updated_at: string;
  buyer?: User;
  products: ProductInOrder[];
  payment_method?: string;
}

export interface Message {
  id: number;
  order_id: number;
  user_id: number;
  message: string;
  created_at: string;
  updated_at: string;
  user: User; // sender
}

export interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  total: number;
  links: PaginationLink[];
  per_page: number;
  path: string;
  from: number | null;
  to: number | null;
}

export interface PaginationLinks {
  first: string | null;
  last: string | null;
  prev: string | null;
  next: string | null;
}

export interface Pagination<T> {
  data: T[];
  meta: PaginationMeta;
  links: PaginationLinks;
}

export interface FlashMessage {
  success?: string;
  error?: string;
  message?: string;
}

export interface CartItem {
  product_id: number;
  name: string;
  cover_image: string;
  price: MoneyData;
  quantity: number;
  stock: number;
}

export interface CheckoutForm {
  cart: Pick<CartItem, 'product_id' | 'quantity'>[];
  purchase?: string;
}

export interface AutocompleteItem {
  title: string;
  value: number;
  product: Product;
}

export type Sentiment = 'positive' | 'neutral' | 'negative' | null;

export interface Feedback {
  id: number;
  user_id: number;
  feedbackable_type: string;
  feedbackable_id: number;
  rating: number;
  comment: string | null;
  sentiment: Sentiment;
  is_verified_purchase: boolean;
  created_at: string;
  author?: {
    id: number;
    name: string;
    avatar?: string;
  };
}

export interface FeedbackForm {
  feedbackable_type: 'product' | 'seller';
  feedbackable_id: number;
  rating: number;
  comment: string;
}

export type TransactionType = 'deposit' | 'withdrawal' | 'transfer' | 'purchase';

export interface Transaction {
  id: number;
  order_id: number | null;
  amount: number;
  formatted_amount: string;
  currency: string;
  type: TransactionType;
  description: string | null;
  created_at: string;
}

export interface PayoutMethod {
  id: number;
  provider: string;
  type: string;
  details: {
    last4?: string;
    bank_name?: string;
    brand?: string;
  };
}

// Echo Events
export interface OrderCreatedEvent {
  order: Order;
}

export interface OrderStatusChangedEvent {
  order_id: number;
  status: Order['status'];
}

export interface MessageSentEvent {
  message: Message;
}

export type BreadcrumbItemType = BreadcrumbItem;
