<?php

declare(strict_types=1);

namespace Autumndev\Checkmend;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use Illuminate\Support\Facades\Log;
use Autumndev\Checkmend\Exceptions\CheckmendInvalidImeiException;
use Autumndev\Checkmend\Exceptions\CheckmendInvalidRequestBody;
use Autumndev\Checkmend\Entities\CheckmendDueDiligenceResult;
use GuzzleHttp\Exception\GuzzleException;
use StdClass;

class Checkmend
{
    /**
     * @param GuzzleHttp\Client
     */
    protected $client;
    /**
     * @param integer
     */
    protected $partnerId;
    /**
     * @param string
     */
    protected $secret;
    /**
     * @param integer
     */
    protected $organisationId;
    /**
     * @param integer
     */
    protected $storeId;
    /**
     * @param bool
     */
    protected $reseller;
    /**
     * @param array
     */
    protected $resellerDetails;

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
        bool $logEnabled,
        float $timeout,
        bool $reseller,
        array $resellerDetails
    ) {
        $handlerStack = HandlerStack::create();
        if ($logEnabled === true) {
            $handlerStack->push(
                Middleware::log(
                    Log::getLogger(),
                    new MessageFormatter('{req_body} - {res_body}'),
                    'debug'
                )
            );
            $handlerStack->push(
                Middleware::log(
                    Log::getLogger(),
                    new MessageFormatter('{uri} - {method} - {code}'),
                    'debug'
                )
            );
        }
        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $baseUri,
            // You can set any number of default request options.
            'timeout'  => $timeout,
            // handler stack for logging purposes
            'handler' => $handlerStack,
        ]);

        $this->partnerId        = $partnerId;
        $this->secret           = $secret;
        $this->organisationId   = $organisationId;
        $this->storeId          = $storeId;
        $this->reseller         = $reseller;
        $this->resellerDetails  = $resellerDetails;
    }

    /**
     * Due Diligence API Calls
     *
     * @param string $imei
     *
     * @return CheckmendDueDiligenceResult
     * @throws CheckmendInvalidImeiException
     */
    public function dueDiligence(string $imei): CheckmendDueDiligenceResult
    {
        //validate IMEI
        if (!$this->validateIMEI($imei)) {
            throw new CheckmendInvalidImeiException("IMEI: {$imei} is not valid");
        }

        $dataPackage = [
            'category' => [1, 2],
        ];

        return new CheckmendDueDiligenceResult(
            $this->sendApiRequest($dataPackage, "/duediligence/{$this->storeId}/{$imei}")
        );
    }

    /**
     * Make & Model Extended API Calls
     *
     * @param array<string> $serials
     *
     * @return StdClass
     * @throws CheckmendInvalidImeiException
     * @throws CheckmendInvalidRequestBody
     * @throws GuzzleException
     */
    public function makeModelExt(array $serials): stdClass
    {
        // check each serial is a valid IMEI
        foreach ($serials as $serial) {
            if (!$this->validateIMEI($serial)) {
                throw new CheckmendInvalidImeiException('The IMEI supplied is not valid');
            }
        }

        $dataPackage = [
            'storeId'   => $this->storeId,
            'category' => [1,2],
            'serials' => $serials,
        ];

        return $this->sendApiRequest($dataPackage, 'makemodelext');
    }

    /**
     *
     * @param string $certificateId
     * @param string|null $url
     * @param string|null $email
     *
     * @return null|StdClass
     * @throws CheckmendInvalidRequestBody
     * @throws GuzzleException
     */
    public function getCertificate(string $certificateId, string $url = null, string $email = null): ?stdClass
    {
        $dataPackage = [];
        if ($url != null) {
            $dataPackage['url'] = $url;
        }

        if ($email != null) {
            $dataPackage['email'] = $email;
        }

        return $this->sendApiRequest($dataPackage, "certificate/{$certificateId}", false);
    }

    /**
     * performs the send request to the API
     *
     * @param array $dataPackage
     * @param string $apiEndPoint
     * @param bool $incReseller
     *
     * @return null|StdClass
     * @throws CheckmendInvalidRequestBody
     * @throws GuzzleException
     */
    protected function sendAPIRequest(array $dataPackage, string $apiEndPoint, bool $incReseller = true): ?stdClass
    {
        if ($this->reseller === true && $incReseller === true) {
            $dataPackage['moreinformation '] = 'Y';
            $dataPackage['more'] = $this->resellerDetails;
            $data['inpossession '] = 'Y';
        }

        $requestBody = json_encode($dataPackage);
        $response = $this->client->post($apiEndPoint, [
            'body'      => $requestBody,
            'headers'   => [
                'Authorization' => 'Basic '.$this->generateAuthHeader($requestBody),
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ],
        ]);

        $r = json_decode((string) $response->getBody());

        // certificate endpoint doesn't return anything
        if (is_string($r)) {
            $r = new stdClass();
            $r->result = 'complete';
            $r->certid = 'Check certificate email for certificate.';

            return $r;
        }

        return null;
    }

    /**
     * generates the Authorisation header
     *
     * @param string $requestBody json encoded request body
     *
     * @return string
     * @throws CheckmendInvalidRequestBody
     */
    protected function generateAuthHeader(string $requestBody): string
    {
        if (!json_encode($requestBody)) {
            throw new CheckmendInvalidRequestBody('The Request body was invalid.');
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
    protected function validateIMEI(string $imei): bool
    {
        if (preg_match('/^[0-9]{15}$/', $imei)) {
            for ($i = 0, $sum = 0; $i < 14; $i++) {
                $tmp = $imei[$i] * (($i%2) + 1);
                $sum += ($tmp%10) + intval($tmp/10);
            }
            return (((10 - ($sum%10)) %10) == $imei[14]);
        }

        return false;
    }
}
