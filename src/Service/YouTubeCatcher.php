<?php

namespace App\Service;

use Google\Client;
use Google\Service\YouTube;

class YouTubeCatcher
{
    public function __construct(
        private string $apiKey
    )
    {
    }

    /**
     * Sends a request to the YouTube API
     *
     * @param string $search
     * @return string
     * @throws \Google\Service\Exception
     */
    public function search(string $search) : string
    {
        $results = [];

        $client = new Client();
        $client->setDeveloperKey($this->apiKey);
        $client->setApplicationName("travel-symfony7");

        $service = new YouTube($client);
        $queryParams = [
            'q' => $search . ' Sehenswürdigkeiten',
            'regionCode' => 'DE',
            'relevanceLanguage' => 'DE',
            'topicId' => '/m/07bxq',
            'type' => 'video',
            'videoEmbeddable' => 'true'
        ];

        try {
            $response = $service->search->listSearch('snippet', $queryParams);

            if ($response->count() > 0) {
                foreach ($response->getItems() as $item) {
                    $results[] = [
                        'id' => $item->getId()->getVideoId(),
                        'title' => $item->getSnippet()->getTitle()
                    ];
                }
            }

            return json_encode($results);
        }
        catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return '';
        }
    }
}