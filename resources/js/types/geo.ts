export type Coordinates = [number, number];

export interface Address {
  country: string;
  city: string;
  street: string;
  house_number: string;
  postal_code: string;
  full_address: string;
}

export interface Location {
  id: number;
  name: string;
  type: LocationType;
  address: Address;
  latitude: number;
  longitude: number;
  external_ids: Record<string, string> | null;
  reviews_count?: number;
  reviews_avg_rating?: string;
  created_at: string;
  updated_at: string;
}

export type LocationType = 'store' | 'pickup_point' | 'warehouse' | 'office';

export interface LocationForm {
  id?: number;
  name: string;
  type: LocationType;
  address: Address;
  latitude: number | null;
  longitude: number | null;
}
