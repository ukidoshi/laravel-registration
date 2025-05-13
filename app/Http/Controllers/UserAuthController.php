<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRegistrationRequest;
use App\Http\Resources\UserAuthResource;
use App\Services\UserAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserAuthController extends Controller
{
    protected $userAuthService;

    public function __construct(UserAuthService $userAuthService)
    {
        $this->userAuthService = $userAuthService;
    }

    /**
     * Register a new user
     *
     * @param UserRegistrationRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(UserRegistrationRequest $request)
    {
        $result = $this->userAuthService->register($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully',
            'user' => new UserAuthResource($result['user']),
            'token' => $result['token']
        ], 201);
    }

    /**
     * Get authenticated user profile
     *
     * @param Request $request
     * @return UserAuthResource
     */
    public function profile(Request $request)
    {
        $user = $this->userAuthService->getUserProfile($request->user());
        
        return new UserAuthResource($user);
    }
}
