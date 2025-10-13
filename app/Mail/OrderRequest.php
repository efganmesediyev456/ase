<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $order;


    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
	$customer_id='';
	$email='';
	if($this->order->user && $this->order->user->customer_id)
	    $customer_id=$this->order->user->customer_id;
	if($this->order->user && $this->order->user->email)
	    $email=$this->order->user->email;
        return $this->subject('New Order Request #' . $this->order->id.' / '.$customer_id)->from('no_replay@' . env('DOMAIN_NAME'))->cc([$email])->view('emails.order_request');
    }
}
