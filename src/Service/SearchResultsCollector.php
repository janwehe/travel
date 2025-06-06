<?php

namespace App\Service;

class SearchResultsCollector
{
    public function __construct(
        private HereMap $map,
        private YouTubeCatcher $youtube
    )
    {
    }

    public function collect(string $search): array
    {
        return [
            'map' => $this->map->search($search),
            'youtube' => $this->youtube->search($search)
        ];
    }
}