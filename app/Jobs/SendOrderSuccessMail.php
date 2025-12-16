<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderSuccessMail;

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
        Mail::to($this->order->email)->send(new OrderSuccessMail($this->order, $this->orderMetas, $this->addOnMetas));
    }
}
