<?php

namespace T2O\User\Mailable;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use T2O\Url\Services\URLService;

class InvitedToJoin extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;

    protected $mailData;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData)
    {
        $this->mailData = $mailData;

        $this->subject = isset($mailData['subject']) ? $mailData['subject'] : 'You were invited to join Blue Perks';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('Email.RateMyTenant.invitedToJoin')
            ->subject($this->subject)
            ->with('mailData', $this->mailData)
            ->withSwiftMessage(function ($message) {
                $message->getHeaders()
                    ->addTextHeader('List-Unsubscribe', (new URLService())->getHttpOrigin() . "/" . config('t2o.links.unsubscribe_list'));
            });
    }
}
