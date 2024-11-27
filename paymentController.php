// PaymentController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentReceipt;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        $ticketId = $request->input('ticketId');
        $numTickets = $request->input('numTickets');
        $mobileNumber = $request->input('mobileNumber');
        $userId = auth()->user()->id;
        $paymentDate = now();

        // Save payment details to the database
        DB::table('payments')->insert([
            'ticket_id' => $ticketId,
            'num_tickets' => $numTickets,
            'mobile_number' => $mobileNumber,
            'user_id' => $userId,
            'payment_date' => $paymentDate,
        ]);

        // Send payment receipt email
        $paymentDetails = [
            'numTickets' => $numTickets,
            'mobileNumber' => $mobileNumber,
            'eventName' => 'Ustun Tickets',
            'eventDate' => '6/11/2024',
            'userName' => auth()->user()->name,
        ];
        Mail::to(auth()->user()->email)
            ->send(new PaymentReceipt($paymentDetails));

        return response()->json(['message' => 'Payment successful']);
    }
}