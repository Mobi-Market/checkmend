<?php
declare(strict_types=1);

namespace Autumndev\Checkmend;

use GuzzleHttp\Client;
use Guzzle\Log\MessageFormatter;
use Illuminate\Support\Facades\Log;
use Autumndev\Checkmend\CheckmendInvalidImeiException;

class Checkmend
{
    /**
     * @param GuzzleHttp\Client
     */
    private $client;
    /**
     * @param integer
     */
    private $partnerId;
    /**
     * @param string
     */
    private $secret;
    /**
     * @param integer
     */
    private $organisationId;
    /**
     * @param integer
     */
    private $storeId;

    /**
     * Sets up require parameters for the api
     *
     * @param integer $partnerId
     * @param string  $secret
     * @param integer $organisationId
     * @param integer $storeId
     * 
     * @return void
     */
    public function __construct(
        string $baseUri,
        int $partnerId, 
        string $secret, 
        int $organisationId, 
        int $storeId,
        bool $logEnabled
    ) {
        $handlerStack = HandlerStack::create();
        if ($logEnabled === true) {
            $handlerStack->push(
                Middleware::log(
                    Log::getMonolog,
                    new MessageFormatter('{req_body} - {res_body}')
                )
            );   
        }
        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $baseUri,
            // You can set any number of default request options.
            'timeout'  => 2.0,
            // handler stack for logging purposes
            'handler' => $handlerStack,
        ]);

        $this->partnerId        = $partnerId;
        $this->secret           = $secret;
        $this->organisationId   = $organisationId;
        $this->storeId          = $storeId;
    }

    /**
     * Due Diligence API Calls
     *
     * @param string $imei
     * 
     * @return string | Excpetion
     */
    public function dueDiligence(string $imei)
    {
        //validate IMEI
        if (!$this->validateIMEI($imei)) {
            throw new CheckmendInvalidImeiException();
        }

        $dataPackage = [
            'category' => [1,2],
        ];

        return $this->sendApiRequest($dataPackage, "/duediligence/{$this->storeid}/{$imei}");
    }
    /**
     * Make & Model Extended API Calls
     *
     * @param array $serials
     * 
     * @return string | Excpetion
     */
    public function makeModelExt(array $serials)
    {
        // check each serial is a valid IMEI
        foreach ($serials as $serial) {
            if (!$this->validateIMEI($imei)) {
                throw new CheckmendInvalidImeiException();
            }
        }

        $dataPackage = [
            'storeId'   => $this->storeId,
            'category' => [1,2],
            'serials' => $serials
        ];
        
        return $this->sendApiRequest($dataPackage, 'makemodelext');
    }

    /**
     * performs the send request to the API
     *
     * @param array  $dataPackage
     * @param string $apiEndPoint
     * 
     * @return string | Excpetion
     */
    private function sendAPIRequest(array $dataPackage, string $apiEndPoint)
    {
        $requestBody = json_encode($dataPackage);
        $response = $this->client->post($apiEndPoint, [
            'body'      => $requestBody,
            'headers'   => [
                'Authorization' => 'Basic '.$this->generateAuthHeader($requestBody),
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json'
            ]
        ]);

        return $response->getBody()->getContents();
    }

    /**
     * generates the Authorisation header
     *
     * @param string $requestBody json encoded request body
     * 
     * @return string
     */
    private function generateAuthHeader(string $requestBody)
    {
        if (!json_encode($requestBody)) {
            throw new CheckmendInvalidRequestBody();
        }
        $base = $this->secret.$requestBody;
        $hash = sha1($base);
        $nonEncoded = $this->partnerId.':'.$hash;
        return base64_encode($nonEncoded);
    }
    /**
     * validates the passed imei
     *
     * @param string $imei
     * 
     * @return bool
     */
    private function validateIMEI(string $imei)
    {
        if (ereg('^[0-9]{15}$', $imei)) {
            
            for ($i = 0, $sum = 0; $i < 14; $i++) {
                $tmp = $imei[$i] * (($i%2) + 1 );
                $sum += ($tmp%10) + intval($tmp/10);
            }
            return (((10 - ($sum%10)) %10) == $imei[14]);
        }
    }
}