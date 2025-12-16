<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( $data )
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->from( $this->data['email'], $this->data['full_name'] )
                    ->to( 'orenwh12@gmail.com' )
                    ->subject( 'New Contact Form Submission' )
                    ->view( 'admin.mail.contact' )
                    ->with( 'data', $this->data );
    }
}
