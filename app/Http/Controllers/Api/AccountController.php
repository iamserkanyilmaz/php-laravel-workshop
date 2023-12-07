<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AccountService;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
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
     * @return JsonResponse
     */
    public function cards(): JsonResponse
    {

        try {
            $cards = $this->subscriptionService->getCardList();
            return response()->json(['status'=> true, 'result' => $cards]);
        } catch (\Exception $ex){
            return response()->json(['status'=> false, 'message' => $ex->getMessage()], $ex->getCode());
        }
    }
}
