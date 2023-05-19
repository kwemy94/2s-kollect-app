<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use App\Repositories\CollectorRepository;

class AuthController extends Controller
{
     /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    private $collectorRepository;
    private $userRepository;

    public function __construct(CollectorRepository $collectorRepository, UserRepository $userRepository)
    {
        $this->middleware('JWT', ['except' => ['login']]);

        $this->collectorRepository = $collectorRepository;
        $this->userRepository = $userRepository;
    } # 'auth:api', ['except' => ['login']]

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);
        // return response()->json([$token = auth()->attempt($credentials)]);
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Email ou mot de passe non correct'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    { 
        auth()->logout();

        return response()->json(['message' => 'Vous êtes hors connexion maintenant !']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $userAuth = $this->userRepository->getById(auth()->user()->id);
        // toggleDatabase(true);
        // $collector = $this->collectorRepository->getCollectorByUserId(auth()->user()->id);

        toggleDatabase(false);
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => $userAuth,
            // 'collector' => $collector,
        ]);
    }
}
