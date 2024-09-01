<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerificationRequest;
use App\Models\User;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function verify(VerificationRequest $request)
    {
        $user = User::where('phone', $request->phone)->first();

        if (!$user || $user->verification_code !== $request->verification_code) {
            return response()->json(['message' => 'Invalid verification code'], 401);
        }

        $user->is_verified = true;
        $user->verification_code = null;
        $user->save();

        return response()->json(['message' => 'Account verified successfully']);
    }
}
