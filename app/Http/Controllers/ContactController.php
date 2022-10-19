<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    //

    public function addContact(Request $request){
        $request->validate([
            'phone' => 'required'
        ]);

        $user = $request->user();

        // check if friend exists in system
        $friend = User::where('phone', $request->phone)->first();
        if($friend){
            // check if he is already friend
            $contact = Contact::where('user_id', $user->id)->where('friend_contact_id', $friend->id)->first();
            // if not friend, send a request
            if(!$contact){
                $contact = Contact::where('friend_contact_id', $user->id)->where('user_id', $friend->id)->first();
                if(!$contact){
                    $contact = Contact::create([
                        'user_id' => $request->user()->id,
                        'friend_contact_id' => $friend->id,
                    ]);
                    // return newly created friend
                    return response()->json([
                        'message' => 'Friend request is sent, wait to be approved.',
                        'contact' => Contact::with('friend', 'user')->where('id', $contact->id)->first()
                    ]);
                }
                else{
                    if($contact->accepted){
                        return response()->json([
                            'message' => 'This contact is already your friend.',
                        ], 500);
                    }
                    else{
                        response()->json([
                            'you have friend request from this contact, please accept it to be friends'
                        ], 500);
                    }
                }
                
            }
            else{
                if(!$contact->accepted){
                    return response()->json([
                        'message' => 'This contact is already your friend, wait for request approval',
                    ], 500);
                }
                return response()->json([
                    'message' => 'This contact is already your friend.',
                ], 500);
            }
        }
        else{
            return response()->json([
                'message' => 'Contact not found',
                'link' => ''
            ], 500);
        }
    }

    public function getContacts(Request $request)
    {
        $user = $request->user();

        $friends = Contact::with('friend', 'user')->where('user_id', $user->id)->orWhere('friend_contact_id', $user->id)->where('accepted', true)->get();

        return response()->json([
            'friends' => $friends,
        ]);

    }

    public function getPendingContacts(Request $request)
    {
        $user = $request->user();

        $friends = Contact::with('friend', 'user')->where('friend_contact_id', $user->id)->where('accepted', false)->get();

        return response()->json([
            'friends' => $friends,
        ]);

    }

    public function acceptPendingContact(Request $request)
    {
        $request->validate([
            'request_id' => 'required'
        ]);

        $user = $request->user();

        $contact = Contact::where('friend_contact_id', $user->id)->where('accepted', false)->first();
        if($contact){
            $contact->accepted = true;
            $contact->save();
            return response()->json([
                'message' => 'Friend request is accepted.',
                'contact' => Contact::with('friend', 'user')->where('id', $contact->id)->first()
            ]);
        }
        else{
            return response()->json([
                'message' => 'No such request found'
            ]);
        }

    }

    
}
