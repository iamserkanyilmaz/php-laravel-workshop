<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{

    /**
     * @var SubscriptionService
     */
    private SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'queue' => 'required',
            'parameters' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=> false, 'message' => $validator->errors()->toArray()], 400);
        }

        try {
            $queue = $request->get('queue');
            $parameters = $request->get('parameters');
            $profile = $parameters['profile'];
            $this->subscriptionService->updateSubscriptionToWebhook($queue['eventType'], $profile);
            return response()->json(['status'=> true, 'message' => 'success']);
        } catch (\Exception $ex){
            return response()->json(['status'=> false, 'message' => $ex->getMessage()]);
        }
    }
}

