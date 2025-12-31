package exchange

import (
	"sync"
	"time"
)

type Store struct {
	mu    sync.RWMutex
	cache map[string]CachedRates
	ttl   time.Duration
}

func NewStore(ttl time.Duration) *Store {
	return &Store{
		cache: make(map[string]CachedRates),
		ttl:   ttl,
	}
}

func (s *Store) Get(base string) (*ExchangeRates, bool) {
	s.mu.RLock()
	defer s.mu.RUnlock()

	item, found := s.cache[base]
	if !found {
		return nil, false
	}

	if time.Now().After(item.ExpiresAt) {
		return nil, false
	}

	return item.Rates, true
}

func (s *Store) Set(base string, rates *ExchangeRates) {
	s.mu.Lock()
	defer s.mu.Unlock()

	s.cache[base] = CachedRates{
		Rates:     rates,
		ExpiresAt: time.Now().Add(s.ttl),
	}
}
