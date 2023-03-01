<?php

namespace App\Library\Youtrack;

use GuzzleHttp\Client;

class Youtrack {
  private $client;

  public function __construct()
  {
    $this->client = new Client([
      // Base URI is used with relative requests
      'base_uri' => 'https://lyonsystems.youtrack.cloud/',
      // You can set any number of default request options.
      'timeout'  => 10.0,
      'headers' => [
        'Authorization' => 'Bearer ' . env("YOUTRACK_TOKEN"),
        'Content-Type' => 'application/json'
      ]
    ]);
  }
  
  public function request($path, $method="GET", $query = "") {
    $response = $this->client->request($method, $path);

    if ($response->getStatusCode() == 200) {
      return json_decode($response->getBody()->getContents());
    }
  }
}