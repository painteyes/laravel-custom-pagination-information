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
        // Aggiunge tutti i parametri di query alla richiesta del paginatore.
        $this->appends(\Request::all());
    
        $window = \Illuminate\Pagination\UrlWindow::make($this);

        /** Il paginatore garantisce un numero fisso di elementi nel layout.
         * 
         * Con $window['slider'] popolato, la disposizione si presenta così:
         * 1. $window['first']: ha due elementi fissi a sinistra (1, 2).
         * 2. $window['slider']: mostra l'elemento corrente, circondato da 'onEachSide' elementi per lato.
         * 3. $window['last']: ha due elementi fissi a destra, speculari agli elementi di $window['first'].
         *
         * L'elemento "..." è un delimitatore predefinito del paginatore per indicare ulteriori pagine tra i gruppi. 
         * Se si modifica il metodo link, "..." deve essere aggiunto manualmente.
         *
         * Con $window['slider'] popolato, avremo due delimitatori "..." attorno allo slider; 
         * altrimenti, solo uno tra $window['first'] e $window['last'].
         *
         * Se $window['slider'] non è presente, abbiamo:
         * 1. $window['first']: due elementi fissi o una composizione di due elementi, un sostituto per "...", l'elemento corrente e 'onEachSide' elementi.
         * 2. $window['slider']: sempre null.
         * 3. $window['last']: due elementi fissi o una disposizione simile a $window['first'], ma speculare.
         *
         * Per mantenere la consistenza:
         * In assenza dello slider, $window['first'] o $window['last'] conterranno una quantità di elementi equivalente a quello dello slider, 
         * più i due elementi fissi e un elemento che sostituisce un "...". 
         */
        $fixedElements = 3;
        if (isset($window['slider'])) {
            $onEachSide = (count($window['slider']) - 1) / 2; // -1 per l'elemento corrente
        } else {
            $onEachSide = (count($window['first']) - $fixedElements - 1) / 2; // -1 per l'elemento corrente
        }

        $firstElement = $this->currentPage() - $onEachSide > 0 ? $this->currentPage() - $onEachSide : 1;

        // 1. Se `$window['first']` ha solo due elementi, non vogliamo aggiungerlo all'output.
        // 2. Se ci sono "..." a sinistra dell'elemento corrente (indicando elementi omessi), non li includiamo.
        // 3. Quando `$window['first']` ha più di due elementi, vogliamo assicurarci che il totale degli elementi rimanga consistente.
        //    Per fare ciò, eliminiamo tre elementi da `$window['first']`.
        // 4. La funzione `array_slice` viene utilizzata per ottenere una porzione di `$window['first']` basata sull'elemento corrente e 
        //    sulla sua posizione relativa. Questo ci permette di mantenere una dimensione costante per la paginazione visualizzata.
        $startIndex = $firstElement - 1;
        $elements = array_filter([
            count($window['first']) > 2 
                ? array_slice($window['first'], $startIndex, count($window['first']) - $fixedElements, true) 
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