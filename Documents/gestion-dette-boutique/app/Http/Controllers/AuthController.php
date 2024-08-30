<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Traits\RestResponseTrait;
use App\Enums\StateEnum;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use RestResponseTrait;

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('login', 'password'))) {
            return $this->sendResponse(null, StateEnum::ECHEC, 'Les identifiants sont incorrects', 401);
        }

        $user = User::where('login', $request->login)->firstOrFail();

        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->plainTextToken;
        $refreshToken = Str::random(60);

        $user->update(['refresh_token' => $refreshToken]);

        return $this->sendResponse([
            'user' => $user,
            'access_token' => $token,
            'token_full' => [
                'id' => $tokenResult->accessToken->id,
                'token' => $token,
                'abilities' => $tokenResult->accessToken->abilities,
            ],
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
        ], StateEnum::SUCCESS, 'Connexion réussie');
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required',
        ]);

        $user = User::where('refresh_token', $request->refresh_token)->first();

        if (!$user) {
            return $this->sendResponse(null, StateEnum::ECHEC, 'Refresh token invalide', 401);
        }

        // Révoquer tous les tokens existants
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;
        $refreshToken = Str::random(60);

        $user->update(['refresh_token' => $refreshToken]);

        return $this->sendResponse([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
        ], StateEnum::SUCCESS, 'Token rafraîchi avec succès');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        $request->user()->update(['refresh_token' => null]);

        return $this->sendResponse(null, StateEnum::SUCCESS, 'Déconnexion réussie');
    }
}
