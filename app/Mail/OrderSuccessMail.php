<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $orderMetas;
    public $addOnMetas;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order, $orderMetas, $addOnMetas)
    {
        $this->order = $order;
        $this->orderMetas = $orderMetas;
        $this->addOnMetas = $addOnMetas;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
                ->subject('Order Confirmation - ' . $this->order->reference)
                ->view('admin.mail.order-success')
                ->with([
                    'order' => $this->order,
                    'orderMetas' => $this->orderMetas,
                    'addOnMetas' => $this->addOnMetas,
                ]);
    }
}
