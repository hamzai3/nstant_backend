<?php

namespace App\Http\Controllers;

use App\Events\PhoneUpdated;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'Account Deleted. You will be logged out.'
        ]);

    }

    public function sendFeedback(Request $request)
    {
        $request->validate([
            'subject' => 'required',
            'message' => 'required'
        ]);

        $user = $request->user();

        $details = [
            'subject' => $request->subject,
            'message' => $request->message
        ];

        try{
            Mail::to(env('MAIL_FROM_ADDRESS'))->send(new \App\Mail\FeedbackMail($details));
            return response()->json([
                'message' => 'Feedback sent.'
            ]);
        }
        catch(Exception $exception){
            return response()->json([
                'message' => 'Try later, something went wrong.'
            ], 500);
        }

    }

    public function updatePhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|unique:users'
        ]);
        
        $phone = $request->phone;
        $code = random_int(100000, 999999);
        $message = 'Your nstant phone verification code is: ' . $code;

        try {

            $user = $request->user();

            $user->phone = $phone;
            $user->verification_code = $code;
            $user->is_phone_verified = false;
            $user->save();

            // revoke all tokens 
            $user->token()->revoke();

            PhoneUpdated::dispatch($phone, $message);

            return response()->json([
                'message' => 'Your phone is updated, wait for the one time code to verify phone.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }


    }

}
