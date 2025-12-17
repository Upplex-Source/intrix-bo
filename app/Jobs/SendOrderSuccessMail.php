<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Helper;

class SendOrderSuccessMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;
    public $orderMetas;
    public $addOnMetas;

    /**
     * Create a new job instance.
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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Render the email view to HTML
        $htmlContent = view('admin.mail.order-success', [
            'order' => $this->order,
            'orderMetas' => $this->orderMetas,
            'addOnMetas' => $this->addOnMetas,
        ])->render();

        // Send email using Brevo
        Helper::sendBrevoEmail(
            $this->order->email,
            $this->order->fullname ?? $this->order->company_name,
            'Order Confirmation - ' . $this->order->reference,
            $htmlContent
        );
    }
}
