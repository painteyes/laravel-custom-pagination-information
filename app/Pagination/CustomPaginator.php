<?php

namespace App\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;

class CustomPaginator extends LengthAwarePaginator
{
    
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        // Aggiunge tutti i parametri di query alla richiesta del paginatore.
        // In questo modo, quando i link di paginazione vengono generati,
        // essi conterranno tutti i parametri di query aggiuntivi oltre al parametro 'page'.
        $this->appends(\Request::all());

        return [
            "current_page" => $this->currentPage(),
            "data" => $this->items->toArray(),
            "first_page_url" => $this->url(1),
            "from" => ($this->currentPage() - 1) * $this->perPage() + 1,
            "last_page" => $this->lastPage(),
            "last_page_url" => $this->url($this->lastPage()),
            "links" => $this->linkCollection()->toArray(),
            "next_page_url" => $this->nextPageUrl(),
            "path" => "http://http://127.0.0.1:8000",
            "per_page" => $this->perPage(),
            "prev_page_url" => $this->previousPageUrl(),
            "to" => min($this->currentPage() * $this->perPage(), $this->total()),
            "total" => $this->total(),
            // 'count' => $this->count(),
        ];
    }
}