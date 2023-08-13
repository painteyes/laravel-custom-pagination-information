<?php

namespace App\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;

class CustomPaginator extends LengthAwarePaginator
{
    /**
     * Override the links method to modify the elements.
     */
    public function links($view = null, $data = [])
    {
        // Aggiungi tutti i parametri di query alle link del paginatore.
        $this->appends(\Request::all());
    
        $window = \Illuminate\Pagination\UrlWindow::make($this);
    
        // Il layout del paginatore mantiene un numero totale di elementi costante. 
        // Considerando la struttura base di quando abbiamo più di 'onEachSide' elementi 
        // sia a sinistra che a destra dell'elemento corrente, questa prevede:
        // 1. Tre elementi fissi a sinistra, rappresentati da (1, 2 e "...").
        // 2. L'elemento corrente al centro, affiancato da 'onEachSide' elementi per lato.
        // 3. Tre elementi fissi a destra, speculari rispetto ai primi tre.
        //
        // Se tutti gli elementi sono concentrati solo a sinistra o solo a destra dell'elemento corrente,
        // il calcolo di 'onEachSide' necessita di alcune modifiche. Nello specifico:
        // - Si escludono i tre elementi estremi, poiché verranno rimpiazzati da (1, 2 e "...").
        // - Si esclude anche l'elemento corrente.
        // Infine, il risultato viene diviso per due per ottenere il valore di 'onEachSide'.
        $onEachSide = (count($window['first']) - 4) / 2;

        $firstElement = $this->currentPage() - $onEachSide > 0 ? $this->currentPage() - $onEachSide : 1;

        $elements = array_filter([
            count($window['first']) > 2 
                ? array_slice($window['first'], $firstElement - 1, count($window['first']) - 3, true) 
                : null,
            $window['slider'],
            count($window['last']) == 2 ? '...' : null,
            $window['last']
        ]);
    
        // Resetta le chiavi dell'array
        $elements = array_values($elements);
    
        // La vista di paginazione di Laravel di default è "pagination::tailwind",
        // ma puoi specificare la tua se necessario.
        $view = $view ?: 'pagination::tailwind';
    
        // Calcola l'URL per i punti (dots)
        $lastSliderPageNumber = key(array_slice($elements[0], -1, 1, true));
        $lastSliderUrl = $elements[0][$lastSliderPageNumber];
        $dotsUrl = preg_replace('/(?<=\bpage=)\d+/', $lastSliderPageNumber + 1, $lastSliderUrl);
    
        return view($view, [
            'paginator' => $this,
            'elements'  => $elements,
            'dotsUrl'   => $dotsUrl
        ]);
    }
    
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
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