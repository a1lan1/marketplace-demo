package main

import (
	"log"
	"net/http"
	"os"
	"time"

	"currency_service/internal/exchange"
	myhttp "currency_service/internal/transport/http"
)

func enableCORS(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Access-Control-Allow-Origin", "*")
		w.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		w.Header().Set("Access-Control-Allow-Headers", "Content-Type")

		if r.Method == "OPTIONS" {
			w.WriteHeader(http.StatusOK)
			return
		}

		next.ServeHTTP(w, r)
	})
}


func main() {
	port := os.Getenv("PORT")
	if port == "" {
		port = "8004"
	}

	store := exchange.NewStore(1 * time.Hour)
	fetcher := exchange.NewFetcher()
	service := exchange.NewService(store, fetcher)
	handler := myhttp.NewHandler(service)

	mux := http.NewServeMux()

	mux.HandleFunc("/health", func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusOK)
		w.Write([]byte(`{"status": "ok"}`))
	})
	handler.RegisterRoutes(mux)

	log.Printf("Starting Currency Service on port %s", port)
	if err := http.ListenAndServe(":"+port, enableCORS(mux)); err != nil {
		log.Fatalf("Server failed to start: %v", err)
	}
}
