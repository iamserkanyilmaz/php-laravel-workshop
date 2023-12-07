<?php

namespace App\Http\Controllers;

use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    /**
     * @var AccountService
     */
    private AccountService $accountService;


    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
        $this->middleware('auth:api', ['except' => ['login','register','refresh','logout']]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required',
            'phone_number' => 'required',
            'ip_address' => 'required',
            'country' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=> false, 'message' => $validator->errors()->toArray()], 400);
        }

        try {

            $user = User::create([
                'name' => $request->get('first_name'),
                'email' => $request->get('email'),
                'password' => Hash::make($request->get('password')),
            ]);

            $token = Auth::guard('api')->login($user);

            $this->accountService->create($request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ], 201);
        } catch (\Exception $ex){
            return response()->json(['status'=> false, 'message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=> false, 'message' => $validator->errors()->toArray()], 400);
        }

        $credentials = $request->only('email', 'password');

        $token = Auth::guard('api')->attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::guard('api')->user();

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);

    }

    /**
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }


    /**
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::guard('api')->user(),
            'authorisation' => [
                'token' => Auth::guard('api')->refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}
