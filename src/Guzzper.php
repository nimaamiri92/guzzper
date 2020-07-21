<?php


namespace Guzzper;


use GuzzleHttp\Client as GuzzleClient;
use Guzzper\Exceptions\GuzzleErr;
use Guzzper\Exceptions\OriginalApiGuzzleError;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TooManyRedirectsException;

class Guzzper
{
    protected $service;
    protected $client;
    protected $response;

    public function verifySSL(){
        $verify = true;
        if (env('APP_ENV') == 'local') {
            $verify = false;
        }
        return $verify;
    }
    public function __construct(string $service)
    {
        $this->service = $service;

        $this->client = new GuzzleClient(['base_uri' => $this->service ,'verify' => $this->verifySSL()]);
    }

    public function send(string $method, string $url, array $params)
    {
        try {
            $response = $this->client->request($method, $url, $params);
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() <= 299) {
                $this->response =  $response->getBody()->getContents();
            } else {
                $this->response =  false;
            }

            return $this;
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $jsonBody = (string)$response->getBody();

            $this->isValidJsonString($jsonBody, function () use ($jsonBody, $response) {
                throw new OriginalApiGuzzleError($jsonBody, $response->getStatusCode());
            });

            throw new GuzzleErr($e->getMessage(), 400, $e->getTrace());
        } catch (TooManyRedirectsException $e) {
            throw new GuzzleErr($e->getMessage(), 400, $e->getTrace());
        } catch (ConnectException $e) {
            throw new GuzzleErr($e->getMessage(), 503, $e->getTrace());
        } catch (ServerException $e) {
            throw new GuzzleErr($e->getMessage(), 500, $e->getTrace());
        } catch (RequestException $e) {
            throw new GuzzleErr($e->getMessage(), 500, $e->getTrace());
        }
    }

    private static function isValidJsonString(string $json, \Closure $caller)
    {
        json_decode($json);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $caller();
        }

        return false;
    }

    public function json()
    {
        return $this->response;
    }

    public function parseToPhp($isArray = false)
    {
        return json_decode($this->response,$isArray);
    }

}