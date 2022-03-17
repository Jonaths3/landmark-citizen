<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (!Auth::guard('web')->attempt($request->only('email', 'password'))) {
            return response([
                'message' => 'Invalid credentials!'
            ]);
        }
        $user = Auth::guard('web')->user();
        if ($user->is_verified == 0) {
            return json_encode(
                array(
                    "responseCode" => "11",
                    "userToken" => $user->user_token
                ));
        }
        $token = $user->createToken('token')->plainTextToken;
        $cookie = cookie('jwt', $token, 60 * 24); // 1 day
        $user_token = $user->user_token;
        // Getting user class name
        $user_class = DB::table('loyalty_classes')->where('id', $user->user_type)->first();
        $user->user_type = $user_class->loyalty_class;

        return json_encode(
            array(
                "message" => "Successful login.",
                "responseCode" => "00",
                "apiToken" => $token,
                "userBlock" => $user
            ));

        
    }

    public function logout() {
        auth()->user()->tokens()->delete();

        return [
            'message' => 'Logged out'
        ];
    }

    
}
