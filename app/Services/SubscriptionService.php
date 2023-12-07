<?php

namespace App\Services;

use App\Http\Adapters\Subscription\Adapter;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionService
{
    const SUBSCRIPTION_EXISTS_CACHE_KEY = 'subscription_id_%s';

    /**
     * @var Adapter
     */
    private Adapter $adapter;

    /**
     * @var AccountService
     */
    private AccountService $accountService;

    /**
     * @param Adapter $adapter
     * @param AccountService $accountService
     */
    public function __construct(Adapter $adapter, AccountService $accountService)
    {
        $this->adapter = $adapter;
        $this->accountService = $accountService;
    }

    /**
     * @param array $subscriptionData
     * @return void
     * @throws \Exception
     */
    public function createSubscription(array $subscriptionData): void
    {
        $account = $this->accountService->getAccountByToken();

        if (!$account){
            throw new \Exception('Account id not found', 400);
        }

        $result = $this->adapter->create($this->createPayload($account, $subscriptionData));

        if ($result['meta']['httpStatus'] !== Response::HTTP_OK)
        {
            throw new \Exception($result['meta']['errorMessage'], $result['meta']['httpStatus']);
        }

        $subscription = new Subscription();
        $subscription->prepareAndSave($account, $result);

        $this->setSubscriptionExistsCache($subscription->getAttributes());
    }

    /**
     * @param int $accountId
     * @return array
     * @throws \Exception
     */
    public function checkSubscription(): array
    {
        $account = $this->accountService->getAccountByToken();

        if (!$account){
            throw new \Exception('Account id not found', 400);
        }

        $subscription = $this->existsSubscriptionExistsCache($account['id']);
        if (!$subscription){
            throw new \Exception('There is no subscription', 400);
        }

        return $subscription;
    }

    /**
     * @param string $cancellationReason
     * @return void
     * @throws \Exception
     */
    public function cancelSubscription(string $cancellationReason): void
    {
        $account = $this->accountService->getAccountByToken();

        if (!$account){
            throw new \Exception('Account id not found', 400);
        }

        $accountId = $account['id'];

        $subscription = $this->existsSubscriptionExistsCache($accountId);
        if (!$subscription){
            throw new \Exception('There is no subscription', 400);
        }

        if ($subscription['status'] !== 'active'){
            throw new \Exception('There is no active subscription', 400);
        }

        $data = $this->cancelPayLoad($accountId, $subscription['package'], $cancellationReason);
        $result = $this->adapter->cancelSubscription($data);

        if ($result['meta']['httpStatus'] !== Response::HTTP_OK)
        {
            throw new \Exception($result['meta']['errorMessage'], $result['meta']['httpStatus']);
        }

        Subscription::whereStatus('active')
            ->whereId($subscription['id'])
            ->update([
                'status' => $result['result']['profile']['status'],
                'real_status' => $result['result']['profile']['realStatus']
            ]);

        $this->refreshSubscriptionExistsCache($accountId);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getCardList(): array
    {
        $account = $this->accountService->getAccountByToken();

        if (!$account){
            throw new \Exception('Account id not found', 400);
        }

        $result = $this->adapter->getCardList(['subscriberId' => $account['id']]);

        if ($result['meta']['httpStatus'] !== Response::HTTP_OK)
        {
            throw new \Exception($result['meta']['errorMessage'], $result['meta']['httpStatus']);
        }

        return $result['result']['cardList'];
    }

    /**
     * @param string $eventType
     * @param array $profile
     * @return void
     * @throws \Exception
     */
    public function updateSubscriptionToWebhook(string $eventType, array $profile): void
    {
        if (!$this->accountService->existsAccountByCache($profile['subscriberId'])){
            throw new \Exception('Account id not found', 400);
        }

        $data = [];
        switch ($eventType){
            case Subscription::EVENT_TYPE_CANCEL:
                $data = ['status' => 'passive', 'real_status' => 'passive'];
                break;
            case Subscription::EVENT_TYPE_RENEWAL:
                $data = ['expire_date' => $profile['expireDate'], 'renewal_date' => $profile['renewalDate']];
                break;
        }

        if (empty($data)){
            throw new \Exception('there is no data change');
        }

        Subscription::query()
            ->where('subscription_id','=', $profile['subscriberId'])
            ->update($data);

        $this->refreshSubscriptionExistsCache($profile['subscriberId']);
    }


    /**
     * @param array $account
     * @param array $subscriptionData
     * @return array
     */
    private function createPayload(array $account, array $subscriptionData): array
    {
        return [
            'cardNo' => $subscriptionData['card_no'],
            'cardOwner' => $subscriptionData['card_owner'],
            'expireMonth' => $subscriptionData['expire_month'],
            'expireYear' => $subscriptionData['expire_year'],
            'cvv' => $subscriptionData['cvv'],
            'packageId' => $subscriptionData['package_id'],
            'platform' => "ios",
            'cardToken' => "",
            'subscriberPhoneNumber' => $account['phone_number'],
            'subscriberFirstname' =>  $account['first_name'],
            'subscriberLastname' =>  $account['last_name'],
            'subscriberEmail' =>  $account['email'],
            'subscriberId' => $account['id'],
            'subscriberIpAddress'=>  $account['ip_address'],
            'subscriberCountry'=>  $account['country'],
            'quantity' => 1,
            'force3ds' => 0,
            'discountPercent' => 0,
            'language' => 'tr'
        ];
    }

    /**
     * @param int $subscriberId
     * @param string $packageId
     * @param string $cancellationReason
     * @return array
     */
    private function cancelPayLoad(int $subscriberId, string $packageId, string $cancellationReason): array
    {
        return [
            'cancellationReason' => $cancellationReason,
            'subscriberId' => $subscriberId,
            'packageId' =>  $packageId,
            'force' => 1
        ];
    }

    /**
     * @param array $subscription
     * @return array
     */
    public function setSubscriptionExistsCache(array $subscription): array
    {
        Cache::add(
            sprintf(self::SUBSCRIPTION_EXISTS_CACHE_KEY, $subscription['account_id']),
            $subscription,
            Carbon::parse($subscription['expire_date'])
        );

        return $subscription;
    }


    /**
     * @param int $subscriberId
     * @return array|null
     */
    public function existsSubscriptionExistsCache(int $subscriberId): array | null
    {
        $subscriptionData = Cache::get(sprintf(self::SUBSCRIPTION_EXISTS_CACHE_KEY, $subscriberId));

        if (!$subscriptionData){
            if ($subscription = Subscription::query()
                ->where('account_id', '=', $subscriberId)
                ->orderBy('updated_at', 'desc')->first()
            ) {
                $subscriptionData = $this->setSubscriptionExistsCache($subscription->getAttributes());
            }
        }

        return $subscriptionData;
    }

    /**
     * @param int $subscriberId
     * @return void
     */
    public function removeSubscriptionExistsCache(int $subscriberId): void
    {
        Cache::forget(sprintf(self::SUBSCRIPTION_EXISTS_CACHE_KEY, $subscriberId));
    }

    /**
     * @param int $subscriberId
     * @return void
     */
    public function refreshSubscriptionExistsCache(int $subscriberId)
    {
        $this->removeSubscriptionExistsCache($subscriberId);
        if ($subscription = Subscription::where('account_id', '=', $subscriberId)->first()) {
            $this->setSubscriptionExistsCache($subscription->getAttributes());
        }
    }

}
