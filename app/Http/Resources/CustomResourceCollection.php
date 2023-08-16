<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;

class CustomResourceCollection extends AnonymousResourceCollection
{
    /**
     * Gestisce le informazioni di paginazione in base alla struttura dell'array $paginated.
     *
     * @param  mixed $request
     * @param  array $paginated L'array ritornato dal metodo toArray del paginatore.
     * @param  array $default Array predefinito con due chiavi (links, meta), utilizzato da Laravel
     *                        per organizzare gli elementi ritornati dal metodo toArray del paginatore.
     *
     * @return array
     *
     * Note:
     * - Se $paginated contiene le chiavi previste di default da Laravel (perché non abbiamo sovrascritto
     *   il metodo toArray del paginatore, o perché lo abbiamo fatto ma mantenendo le stesse chiavi),
     *   Laravel popola correttamente le chiavi 'links' e 'meta'.
     * - Se $paginated contiene chiavi diverse, Laravel inserisce tali elementi in 'meta'.
     * - Per personalizzare la risposta e includere solo alcune chiavi specifiche in caso di
     *   personalizzazione del metodo toArray del paginatore, è necessario specificarlo 
     *   (ad esempio, usando Arr::only($paginated, $customKeys)).
     */
    public function paginationInformation($request, $paginated, $default)
    {   
        $meta = $default['meta']; 
        $metaLinks = $meta['links']; // oppure $this->paginator->linkCollection()->toArray();
        dd($meta['links']);

        $currentPage = $paginated['current_page']; // oppure $this->paginator->currentPage();

        $leftDotsFound = $metaLinks[3]['label'] === '...';
        $rightDotsFound = $metaLinks[count($metaLinks) - 4]['label'] === '...';

        // Determina se i "..." sono presenti all'inizio o alla fine dell'array di metaLinks
        $leftDotsPresent = isset($metaLinks[3]) && $metaLinks[3]['label'] === '...';
        $rightDotsPresent = isset($metaLinks[count($metaLinks) - 4]) && $metaLinks[count($metaLinks) - 4]['label'] === '...';

        // Se i "..." sono presenti solo a destra
        if ($rightDotsPresent && !$leftDotsPresent) {
            $dotsIndex = array_search('...', array_column($metaLinks, 'label'));

            $elementsBeforeRightDots = array_slice($metaLinks, 1, $dotsIndex - 1);

            $fixedElements = [
                // 0 => "Previous"
                1 => "1",
                2 => "2",
                3 => "3"
            ];

            // Calcola il numero di elementi ai lati dell'elemento corrente
            $onEachSideElements = (count($elementsBeforeRightDots) - count($fixedElements) - 1) / 2; // -1 per l'elemento corrente

            $startIndex = max(1, $currentPage - $onEachSideElements);

            $elementsBeforeRightDotsTrimmed = array_slice($elementsBeforeRightDots, $startIndex, count($elementsBeforeRightDots) - count($fixedElements), true);

            $afterDots = array_slice($metaLinks, $dotsIndex + 1);
            
            $lastElemBeforeDots = end($elementsBeforeRightDotsTrimmed);
            $pageValue = intval($lastElemBeforeDots['label']) + 1;
            $newUrl = preg_replace('/(?<=\bpage=)\d+/', $pageValue, $lastElemBeforeDots['url']);

            $dotsElement = $metaLinks[$dotsIndex];
            $dotsElement['url'] = $newUrl;

            $metaLinks = array_merge(
                [ $metaLinks[0] ],
                $elementsBeforeRightDotsTrimmed,
                [ $dotsElement ],
                $afterDots
            );
        }

        // Se i "..." sono presenti a sinistra
        if ($leftDotsPresent) {

            $elementsToRemove = [
                1 => "1",
                2 => "2",
                3 => "..."
            ];

            $metaLinks = array_values(
                array_filter($metaLinks, function ($key) use ($elementsToRemove) {
                    return !isset($elementsToRemove[$key]);
                }, ARRAY_FILTER_USE_KEY)
            );
        }

        $meta['links']= $metaLinks;

        return [
            "links" => $default['links'],
            "meta" => $meta
        ];
    }
}
