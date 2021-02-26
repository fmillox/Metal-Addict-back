<?php

namespace App\Service;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;

class FanartApi
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

  public function getImages(string $artistMbid)
  {
    $httpClient = HttpClient::create();
    $response = $httpClient->request('GET', $this->baseUrl.'/music/'.$artistMbid.'?api_key='.$this->apiKey);
    
    if ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
      return [
        'hdmusiclogo' => [],
        'artistbackground' => [],
        'musiclogo' => [],
        'artistthumb' => [],
        'musicbanner' =>  []
      ];
    }

    $content = json_decode($response->getContent(), true);
    unset($content['name']);
    unset($content['mbid_id']);
    unset($content['albums']);
    
    return $content;
  }
}