<?php
namespace App\Http\Adapters\Subscription;

use Illuminate\Support\Facades\Http;
use \Illuminate\Http\Client\PendingRequest;

class Adapter {
    /**
     * @var string|mixed
     */
    private string $endpoint;

    /**
     * @var string|mixed
     */
    private string $accessKey;

    /**
     * @var string|mixed
     */
    private string $accessSecret;

    /**
     * @var string|mixed
     */
    private string $appId;

    public function __construct()
    {
        $this->endpoint = env('Z_ENDPOINT');
        $this->accessKey = env('Z_ACCESS_KEY');
        $this->accessSecret = env('Z_ACCESS_SECRET');
        $this->appId = env('Z_APP_ID');
    }

    /**
     * @return PendingRequest
     */
    private function client(): PendingRequest
    {
        return Http::withHeaders([
            'AccessKey' => $this->accessKey,
            'AccessSecret' => $this->accessSecret,
            'ApplicationId' => $this->appId,
            'Content-Type' => 'application/json',
            'Language' => 'en',
        ]);
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data) : array
    {
        return $this->client()->post($this->endpoint.'/v1/payment/credit-card', $data)->json();
    }

    /**
     * @param array $data
     * @return array
     */
    public function getCardList(array $data) : array
    {
        return $this->client()->get($this->endpoint.'/v1/subscription/card-list', $data)->json();
    }

    /**
     * @param array $data
     * @return array
     */
    public function checkSubscription(array $data) : array
    {
        $result = $this->client()->get($this->endpoint.'/v1/subscription/profile', $data)->json();
        $profile = $result['result']['profile'];
        return  [
            'startDate' => $profile['startDate'],
            'expireDate' => $profile['expireDate'],
            'renewalDate' => $profile['renewalDate'],
            'status' => $profile['status'],
            'realStatus' => $profile['realStatus']
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function cancelSubscription(array $data): array
    {
        return $this->client()->post($this->endpoint.'/v1/subscription/cancellation', $data)->json();
    }
}
