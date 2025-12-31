package exchange

import "time"

// ExchangeRates represents the response structure from Frankfurter API
type ExchangeRates struct {
	Amount float64            `json:"amount"`
	Base   string             `json:"base"`
	Date   string             `json:"date"`
	Rates  map[string]float64 `json:"rates"`
}

// CachedRates wrapper to track expiration
type CachedRates struct {
	Rates     *ExchangeRates
	ExpiresAt time.Time
}
