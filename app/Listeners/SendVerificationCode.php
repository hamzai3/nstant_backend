<?php

namespace App\Listeners;

use Aloha\Twilio\Twilio;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Twilio\Rest\Client;


class SendVerificationCode
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $sdk = new Twilio(env('TWILIO_SID'), env('TWILIO_TOKEN'), env('TWILIO_FROM'));
        // $client = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
        $sdk->message($event->phone, $event->message);

        // $client->messages->create($event->phone, array(
        //     'From' => env('TWILIO_FROM'),
        //     'Body' => $event->message
        // ));

        // $twilio = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));

        // $twilio->messages
        //     ->create(
        //     $event->phone, // to 
        //         array(
        //             "messagingServiceSid" => env('SERVICE_SID'),
        //             "body" => $event->message
        //         )
        //     ); 
    }
}
