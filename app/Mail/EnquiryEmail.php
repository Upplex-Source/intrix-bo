<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Barryvdh\DomPDF\Facade\Pdf as PDF;

class EnquiryEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // from
        return $this->from('support@infinitedesign.my', 'Yobe Froyo')
        ->subject('Enquiry')
        ->view('admin/mail/enquiry')
        ->with(['data' => $this->data]);
    }
}
