<?php

namespace App\Service;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;

class SetlistApi
{
  /**
   * @var string
   */
  private $baseUrl;

  /**
   * @var string
   */
  private $apiKey;

  public function __construct(string $baseUrl, string $apiKey)
  {
    $this->baseUrl = $baseUrl;
    $this->apiKey = $apiKey;
  }

  public function searchSetlists(string $artistMbid, array $query)
  {
    $httpClient = HttpClient::create();
    $response = $httpClient->request('GET', $this->baseUrl.'/search/setlists?artistMbid='.$artistMbid, [
        'headers' => [
            'Accept' => 'application/json',
            'x-api-key' => $this->apiKey
        ],
        'query' => $query,
    ]);
    
    if ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
      return [
        'type' => 'setlists',
        'itemsPerPage' => 20,
        'page' => 1,
        'total' => 0,
        'setlist' => []
      ];
    }

    return json_decode($response->getContent(), true);
  }

  public function findSetlist(string $setlistId)
  {
    $httpClient = HttpClient::create();
    $response = $httpClient->request('GET', $this->baseUrl.'/setlist/'.$setlistId, [
        'headers' => [
            'Accept' => 'application/json',
            'x-api-key' => $this->apiKey
        ]
    ]);
    
    $result = ['httpStatusCode' => $response->getStatusCode()];
    if ($result['httpStatusCode'] === Response::HTTP_OK) {
      $result = array_merge($result, json_decode($response->getContent(), true));
    }

    return $result;
  }
}