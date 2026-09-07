<?php

namespace App\Http\Controllers;

use App\Models\ExpoToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpoTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        $user = Auth::user();

        ExpoToken::updateOrCreate(
            ['token' => $request->token],
            ['user_id' => $user->id, 'enabled' => true, 'last_used_at' => now()]
        );

        return response()->json([
            'ok' => true,
            'message' => 'Token registrado correctamente'
        ]);
    }
}
