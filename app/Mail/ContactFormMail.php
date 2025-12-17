<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Helper;

class ContactFormMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( $data )
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Render the email view to HTML
        $htmlContent = view('admin.mail.contact', [
            'data' => $this->data,
        ])->render();

        // Send email using Brevo
        Helper::sendBrevoEmail(
            'ecommerce@intrixgroup.com',
            'Admin',
            'New Contact Form Submission',
            $htmlContent
        );
    }
}
