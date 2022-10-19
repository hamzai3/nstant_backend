<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\User;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    //

    public function sendImage(Request $request){
        $request->validate([
            'image' => 'required',
        ]);

        $user = $request->user();

        if($user){
            // send the image to specified user or to all users
            $image = new Image();
            $image->sender_id = $user->id;
            // add image to server
            $file = $request->file('image');
            $destinationPath = 'uploads/user_images/' . $user->id;
            $file_uploaded = $file->move($destinationPath, $file->getClientOriginalName());
            $full_unique_path = $file_uploaded->getPath() . '/' . $file_uploaded->getFilename();
            $image->image_path = asset($full_unique_path);

            // if there is specific receiver than set it
            if($request->receiver_id){
                $receiver = User::find($request->receiver_id);
                if($receiver){
                    $image->receiver_id = $receiver->id;
                }
            }


            $image->save();

            return response()->json([
                'message' => 'Image sent.'
            ]);
        }
        return response()->json([
            'message' => "User not found or invalid token",
        ], 500);

    }

    public function getImages(Request $request){
        $user = $request->user();

        if($user){
            $images_sent = Image::with('sender', 'receiver')->where('sender_id', $user->id)->get();
            $images_received = Image::with('sender', 'receiver')->where('receiver_id', $user->id)->get();

            return response()->json([
                'images_sent' => $images_sent,
                'images_received' => $images_received,
            ]);

        }
    }
}
