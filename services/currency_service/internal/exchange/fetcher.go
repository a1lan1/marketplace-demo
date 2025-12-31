package exchange

import (
	"encoding/json"
	"fmt"
	"net/http"
	"time"
)

type Fetcher struct {
	client *http.Client
	apiURL string
}

func NewFetcher() *Fetcher {
	return &Fetcher{
		client: &http.Client{Timeout: 10 * time.Second},
		apiURL: "https://api.frankfurter.app",
	}
}

func (f *Fetcher) FetchRates(baseCurrency string) (*ExchangeRates, error) {
	url := fmt.Sprintf("%s/latest?base=%s", f.apiURL, baseCurrency)

	resp, err := f.client.Get(url)
	if err != nil {
		return nil, fmt.Errorf("failed to fetch rates: %w", err)
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		return nil, fmt.Errorf("unexpected status code: %d", resp.StatusCode)
	}

	var rates ExchangeRates
	if err := json.NewDecoder(resp.Body).Decode(&rates); err != nil {
		return nil, fmt.Errorf("failed to decode response: %w", err)
	}

	return &rates, nil
}
