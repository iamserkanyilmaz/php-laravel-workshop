<?php

namespace App\Jobs;

use App\Http\Adapters\Subscription\Adapter;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckSubscription implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var integer
     */
    private int $accountId;

    /**
     * @var SubscriptionService
     */
    private SubscriptionService $subscriptionService;

    /**
     * @var Adapter|null
     */
    private ?Adapter $adapter = null;

    /**
     * Create a new job instance.
     */
    public function __construct(int $accountId, SubscriptionService $subscriptionService)
    {
        $this->accountId = $accountId;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * @return Adapter
     */
    private function getAdapter(): ?Adapter
    {
        if ($this->adapter == null)
        {
            $this->adapter = new Adapter();
        }

        return $this->adapter;
    }

    /**
     * Execute the job.
     * @throws \Exception
     */
    public function handle(): void
    {
        $subscription = $this->subscriptionService->existsSubscriptionExistsCache($this->accountId);

        if (!$subscription){
            throw new \Exception('there is no subscription');
        }

        $subData = [
            'subscriberId' => $subscription['account_id'],
            'packageId'=> $subscription['package']
        ];

        $data = $this->getAdapter()->checkSubscription($subData);
        $sideSub = change_array_keys($subscription, true);
        if ($sideSub != $data){
            Subscription::whereAccountId($this->accountId)->orderBy('updated_at', 'desc')->update(change_array_keys($data));
            $this->subscriptionService->refreshSubscriptionExistsCache($this->accountId);
        }
    }
}
