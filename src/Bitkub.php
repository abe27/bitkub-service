<?php

namespace Abe27\Bitkub;

use Abe27\Bitkub\Exceptions\BitkubException;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

class Bitkub
{
    /**
     * API Key
     *
     * @var string
     */
    protected $apiKey;

    /**
     * API Secret
     *
     * @var string
     */
    protected $apiSecret;

    /**
     * API Endpoint
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Create a new Bitkub instance.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'];
        $this->apiSecret = $config['api_secret'];
        $this->endpoint = $config['endpoint'];
    }

    /**
     * Get server status.
     *
     * @return array
     * @throws \Abe27\Bitkub\Exceptions\BitkubException
     */
    public function status()
    {
        return $this->sendRequest('GET', '/api/status');
    }

    /**
     * Get server time.
     *
     * @return array
     * @throws \Abe27\Bitkub\Exceptions\BitkubException
     */
    public function serverTime()
    {
        return $this->sendRequest('GET', '/api/servertime');
    }

    /**
     * Get market symbols.
     *
     * @return array
     * @throws \Abe27\Bitkub\Exceptions\BitkubException
     */
    public function symbols()
    {
        return $this->sendRequest('GET', '/api/market/symbols');
    }

    /**
     * Get ticker information.
     *
     * @param string|null $symbol
     * @return array
     * @throws \Abe27\Bitkub\Exceptions\BitkubException
     */
    public function ticker($symbol = null)
    {
        $endpoint = '/api/market/ticker';
        if ($symbol) {
            $endpoint .= '?sym=thb_' . Str::lower($symbol);
        }
        return $this->sendRequest('GET', $endpoint);
    }

    /**
     * Get user balances (requires authentication).
     *
     * @return array
     * @throws \Abe27\Bitkub\Exceptions\BitkubException
     */
    public function balances()
    {
        $timestamp = (string) round(microtime(true) * 1000);
        $payload = [];
        array_push($payload, $timestamp);
        array_push($payload, 'POST');
        array_push($payload, '/api/v3/market/wallet');
        array_push($payload, "");
        $payloadString = implode('', $payload);
        $signature = $this->generateSignature($payloadString);
        return $this->sendRequest("POST", "/api/v3/market/wallet", $timestamp, $signature, [], true);
    }

    /**
     * Create a buy order (requires authentication).
     *
     * @param string $symbol
     * @param float $amount
     * @param float $rate
     * @param string $type
     * @return array
     * @throws \Abe27\Bitkub\Exceptions\BitkubException
     */
    public function createBuyOrder($symbol, $amount, $rate, $type = 'limit')
    {
        $params = [
            'sym' => "THB_" . $symbol,
            'amt' => $amount,
            'rat' => $rate,
            'typ' => $type,
        ];

        return $this->sendRequest('POST', '/api/market/place-bid', $params, true);
    }

    /**
     * Create a sell order (requires authentication).
     *
     * @param string $symbol
     * @param float $amount
     * @param float $rate
     * @param string $type
     * @return array
     * @throws \Abe27\Bitkub\Exceptions\BitkubException
     */
    public function createSellOrder($symbol, $amount, $rate, $type = 'limit')
    {
        $params = [
            'sym' => "THB_" . $symbol,
            'amt' => $amount,
            'rat' => $rate,
            'typ' => $type,
        ];

        return $this->sendRequest('POST', '/api/market/place-ask', $params, true);
    }

    /**
     * Cancel an order (requires authentication).
     *
     * @param string $symbol
     * @param int $id
     * @param string $side
     * @return array
     * @throws \Abe27\Bitkub\Exceptions\BitkubException
     */
    public function cancelOrder($symbol, $id, $side)
    {
        $params = [
            'sym' => "THB_" . $symbol,
            'id' => $id,
            'sd' => $side,
        ];

        return $this->sendRequest('POST', '/api/market/cancel-order', $params, true);
    }

    /**
     * Generate signature for API request.
     *
     * @param string $params
     * @return string
     */
    protected function generateSignature($params)
    {
        // $queryString = http_build_query($params);
        return hash_hmac('sha256', $params, $this->apiSecret);
    }

    /**
     * Send request to Bitkub API.
     *
     * @param string $method
     * @param string $uri
     * @param array $params
     * @param bool $auth
     * @return array
     * @throws \Abe27\Bitkub\Exceptions\BitkubException
     */
    protected function sendRequest($method, $uri, $timestamp = null, $signature = null, $params = [], $auth = false)
    {
        $url = $this->endpoint . $uri;
        $headers = [];

        if ($auth) {
            // 5️⃣ กำหนด Headers
            $headers = [
                'Accept'          => 'application/json',
                'Content-Type'    => 'application/json',
                'X-BTK-TIMESTAMP' => $timestamp,
                'X-BTK-SIGN'      => $signature,
                'X-BTK-APIKEY'    => $this->apiKey
            ];
            // 6️⃣ ส่ง Request
            try {
                $client = new Client();
                $response = $client->request($method, $url, [
                    'headers' => $headers,
                    'form_params' => $params
                ]);
                $result = json_decode($response->getBody(), true);
                if (isset($result['error']) && $result['error'] !== 0) {
                    throw new BitkubException($result['message'] ?? 'Unknown error', $result['error']);
                }

                return $result;
            } catch (\Exception $e) {
                if ($e instanceof BitkubException) {
                    throw $e;
                }

                throw new BitkubException('Failed to communicate with Bitkub API: ' . $e->getMessage());
            }
        }

        try {
            $client = new Client();
            $response = $client->request($method, $url, [
                'headers' => $headers
            ]);
            $result = json_decode($response->getBody(), true);
            if (isset($result['error']) && $result['error'] !== 0) {
                throw new BitkubException($result['message'] ?? 'Unknown error', $result['error']);
            }

            return $result;
        } catch (\Exception $e) {
            if ($e instanceof BitkubException) {
                throw $e;
            }

            throw new BitkubException('Failed to communicate with Bitkub API: ' . $e->getMessage());
        }
    }
}
