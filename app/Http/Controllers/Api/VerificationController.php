<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\UserRegistered;
use App\Models\User;
use Aloha\Twilio\Twilio;

class VerificationController extends Controller
{
    public function getCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|unique:users',
        ]);

        $phone = $request->phone;
        $code = random_int(100000, 999999);
        $message = 'Your nstant phone verification code is: ' . $code;
        try {

            $user = User::create(
                [
                    'name' => 'user',
                    'phone' => $phone,
                    'email' => 'temp_user_' . $phone . '@temp_mail.com',
                    'password' => bcrypt($code),
                    'verification_code' => $code,
                    'is_phone_verified' => false,
                ]
            );

            UserRegistered::dispatch($phone, $message);

            $token = $user->createToken('mobile_app')->accessToken;

            return response()->json([
                'message' => 'Your account is registerd, wait for the one time password.',
                'token' => $token
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required',
        ]);
        $user = $request->user();
        if ($user) {
            if ($user->verification_code == $request->code) {
                $user->is_phone_verified = true;
                $user->save();

                return response()->json([
                    'message' => 'Phone number is verified',
                    'token' => $request->bearerToken()
                ], 200);
            }
            return response()->json([
                'message' => 'Verification code is incorrect',
                'errors' => [
                    'verification_code' => 'Verification code is incorrect'
                ]
            ], 422);
        }

        return response()->json([
            'message' => 'Invaild Token',
            'errors' => [
                'token' => 'Please attach the token in the header to access protected resources.'
            ]
        ], 401);
    }

    public function updateUser(Request $request)
    {

        $user = $request->user();

        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required | unique:users,email,' . $user->id,
            // 'password' => 'required',
        ]);

        if ($user) {
            $user->name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->save();

            return response()->json([
                'message' => 'Account details are updated',
                'token' => $request->bearerToken(),
                'user' => $user
            ], 200);
        }

        else{
            return response()->json([
                'message' => 'Invaild Token',
                'errors' => [
                    'token' => 'Please attach the token in the header to access protected resources.'
                ]
            ], 401);
        }
    }

    public function getUser(Request $request)
    {
        if($request->user()){
            return response()->json([
                'user' => $request->user()
            ]);
        }

        else{
            return response()->json([
                'message' => 'Invaild Token',
                'errors' => [
                    'token' => 'Please attach the token in the header to access protected resources.'
                ]
            ], 401);
        }
    }

    public function signIn(Request $request){
        $request->validate([
            'phone' => 'required'
        ]);

        $user = User::where('phone', $request->phone)->first();

        if($user){
            
            $code = random_int(100000, 999999);
            $message = 'Your verification code is: ' . $code;

            $user->verification_code = $code;
            $user->save();

            $sdk = new Twilio(env('TWILIO_SID'), env('TWILIO_TOKEN'), env('TWILIO_FROM'));
            $sdk->message($request->phone, $message);

            return response()->json([
                'message' => 'One time password has been send to your phone, please enter your code to continue sign in'
            ]);

        }
        else{
            return response()->json([
                'message' => 'No account found with given phone, register now.',
            ], 500);
        }

    }

    public function verifySignIn(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'code' => 'required'
        ]);

        $user = User::where('phone', $request->phone)->first();
        if($user){
            if($user->verification_code == $request->code){
                $token = $user->createToken('mobile_app')->accessToken;
                return response()->json([
                    'token' => $token,
                    'user' => $user
                ]);
            }
            else{
                return response()->json([
                    'message' => 'Incorrect verification code, try agian.'
                ]);
            }

        }
        else{
            return response()->json([
                'message' => 'User with given phone number does not exist', 
            ]);
        }
    }



}
