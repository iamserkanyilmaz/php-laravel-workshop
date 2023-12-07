<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request) : JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'card_no' => 'required',
            'card_owner' => 'required',
            'expire_month' => 'required',
            'expire_year' => 'required',
            'cvv' => 'required',
            'package_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=> false, 'message' => $validator->errors()->toArray()], 400);
        }

        try {
            $this->subscriptionService->createSubscription($request->all());
            return response()->json(['status'=> true, 'message' => 'subscription created'], 201);
        } catch (\Exception $ex){
            return response()->json(['status'=> false, 'message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function check(Request $request) : JsonResponse
    {
        try {
            $result = $this->subscriptionService->checkSubscription($request->request->get('account_id'));
            return response()->json(['status'=> true, 'result' => $result]);
        } catch (\Exception $ex){
            return response()->json(['status'=> false, 'message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function cancel(Request $request) : JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=> false, 'message' => $validator->errors()->toArray()], 400);
        }

        try {
            $cancellationReason = $request->get('cancellation_reason');
            $this->subscriptionService->cancelSubscription($cancellationReason);
            return response()->json(['status'=> true, 'message' => 'active subscription has been cancelled.']);
        } catch (\Exception $ex){
            return response()->json(['status'=> false, 'message' => $ex->getMessage()], $ex->getCode());
        }
    }
}
