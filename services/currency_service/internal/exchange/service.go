package exchange

import (
	"fmt"
)

type Service struct {
	store   *Store
	fetcher *Fetcher
}

func NewService(store *Store, fetcher *Fetcher) *Service {
	return &Service{
		store:   store,
		fetcher: fetcher,
	}
}

func (s *Service) GetRates(base string) (*ExchangeRates, error) {
	// 1. Check cache
	if rates, found := s.store.Get(base); found {
		return rates, nil
	}

	// 2. Fetch from API
	rates, err := s.fetcher.FetchRates(base)
	if err != nil {
		return nil, err
	}

	// 3. Update cache
	s.store.Set(base, rates)

	return rates, nil
}

func (s *Service) Convert(from, to string, amount float64) (float64, error) {
	if from == to {
		return amount, nil
	}

	rates, err := s.GetRates(from)
	if err != nil {
		return 0, err
	}

	rate, ok := rates.Rates[to]
	if !ok {
		return 0, fmt.Errorf("currency code %s not found", to)
	}

	return amount * rate, nil
}
