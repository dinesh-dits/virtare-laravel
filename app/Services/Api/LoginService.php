<?php

namespace App\Services\Api;

use Exception;

class LoginService
{
    // logout
    public function logout($request)
    {
        try {
            auth()->logout();
            return response()->json(['message' => 'User successfully signed out']);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
