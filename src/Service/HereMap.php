<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class HereMap
{
    public function __construct(
        private HttpClientInterface $client,
        private string $apiKey
    )
    {
    }

    /**
     * Sends a request to the HERE Map API
     *
     * @param string $search
     * @return string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function search(string $search): string
    {
        try {
            $response = $this->client->request(
                'GET',
                'https://geocode.search.hereapi.com/v1/geocode?q=' . urlencode($search) . '&apiKey=' . $this->apiKey
            );

            if ($response->getStatusCode() === 200) {
                return $response->getContent();
            }
        }
        catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return '';
        }
    }
}