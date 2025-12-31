package http

import (
	"encoding/json"
	"net/http"
	"strconv"

	"currency_service/internal/exchange"
)

type Handler struct {
	service *exchange.Service
}

func NewHandler(service *exchange.Service) *Handler {
	return &Handler{service: service}
}

func (h *Handler) RegisterRoutes(mux *http.ServeMux) {
	mux.HandleFunc("/rates", h.HandleGetRates)
	mux.HandleFunc("/convert", h.HandleConvert)
}

func (h *Handler) HandleGetRates(w http.ResponseWriter, r *http.Request) {
	base := r.URL.Query().Get("base")
	if base == "" {
		base = "USD"
	}

	rates, err := h.service.GetRates(base)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(rates)
}

func (h *Handler) HandleConvert(w http.ResponseWriter, r *http.Request) {
	from := r.URL.Query().Get("from")
	to := r.URL.Query().Get("to")
	amountStr := r.URL.Query().Get("amount")

	if from == "" || to == "" || amountStr == "" {
		http.Error(w, "missing required parameters: from, to, amount", http.StatusBadRequest)
		return
	}

	amount, err := strconv.ParseFloat(amountStr, 64)
	if err != nil {
		http.Error(w, "invalid amount", http.StatusBadRequest)
		return
	}

	result, err := h.service.Convert(from, to, amount)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest) // User error (e.g. invalid currency) or internal
		return
	}

	response := map[string]interface{}{
		"from":   from,
		"to":     to,
		"amount": amount,
		"result": result,
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(response)
}
