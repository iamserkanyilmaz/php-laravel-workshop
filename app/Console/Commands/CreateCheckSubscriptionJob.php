<?php

namespace App\Console\Commands;

use App\Jobs\CheckSubscription;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateCheckSubscriptionJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-check-subscription-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * @var SubscriptionService
     */
    private SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        parent::__construct();
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subscriptions = Subscription::query()->select('id','account_id', 'package')
            ->where('status', 'active')
            ->where('expire_date', '>', Carbon::now())->get();
        foreach ($subscriptions as $subscription) {
            $checkSub = new CheckSubscription($subscription->account_id, $this->subscriptionService);
            dispatch($checkSub);
        }
    }
}
