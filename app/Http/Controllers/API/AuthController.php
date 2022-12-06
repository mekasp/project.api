<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Nette\Schema\ValidationException;

class AuthController
{
    public function createToken(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required'],
            'device_name' => ['required']
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->token, $user->token)) {
            throw new ValidationException('user is invalid');
        } else {
            return $user->createToken($request->device_name)->plainTextToken;
        }
    }

}

