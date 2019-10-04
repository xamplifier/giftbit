<?php
declare(strict_types = 1);

namespace Xamplifier\Giftbit;


class Giftbit
{

    protected const TEST_URL = 'https://api-testbed.giftbit.com/papi/v1/';
    protected const PROD_URL = 'https://api.giftbit.com/papi/v1';


    /**
    * Sends HTTP requests using Guzzle
    *
    * @param string $url
    * @param array $data
    * @param string $requestMethod
    * @param string $requestType
    *
    * @return array
    */
    protected function execute(string $url, array $data = [], string $requestMethod, string $requestType) :array
    {
        $config = include('config.php');

        $client = new \GuzzleHttp\Client(['base_uri' => self::TEST_URL]);

        $headers = [
          'Authorization' => 'Bearer ' . $config['token'],
          'Accept'        => 'application/json',
          'Content-Type'  => 'application/json',
        ];

        $response = $client->request($requestMethod, $url, [
          'headers' => $headers,
          $requestType => $data
        ]);


       return $this->parseResponse($response);

    }


    /**
    * Verifies your authenication
    * @return array
    */
    public static function ping():array
    {
        $data = [];
        $gb = new Giftbit;

        return $gb->execute('ping', $data, "GET");

    }


    /**
    * Validates the status code received from the HTTP request and formats the response.
    *
    * @param object $response The result of the HTTP request
    *
    * @return array
    */
    protected function parseResponse(object $response) :array
    {
        $code = $response->getStatusCode();

        if ($code == 201 || $code == 200 || $code == 202) {
            return ['success' => true, 'data' => json_decode($response->getBody()->getContents(), true)];
        } else {
            return ['success' => false, 'data' => json_decode($response->getBody(), true)];
        }
    }

}
