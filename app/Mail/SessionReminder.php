<?php

namespace App\Mail;

use App\Models\judicial_affairs\Session;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SessionReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /*
    |--------------------------------------------------------------------------
    | User Data
    |--------------------------------------------------------------------------
    */
    public $user;

    /*
    |--------------------------------------------------------------------------
    | System Settings
    |--------------------------------------------------------------------------
    */
    public $settings;

    /*
    |--------------------------------------------------------------------------
    | Session Data and Time
    |--------------------------------------------------------------------------
    */
    public $sessionDateTime;

    /*
    |--------------------------------------------------------------------------
    | Session Data
    |--------------------------------------------------------------------------
    */
    public $session;

    /*
    |--------------------------------------------------------------------------
    | Constract
    |--------------------------------------------------------------------------
    */
    public function __construct(User $user, Settings $settings, string $sessionDateTime, Session $session)
    {
        $this->user = $user;
        $this->settings = $settings;
        $this->sessionDateTime = $sessionDateTime;
        $this->session = $session;
    }

    /*
    |--------------------------------------------------------------------------
    | Build
    |--------------------------------------------------------------------------
    */
    public function build()
    {
        return $this->subject('تذكير: ' . $this->session->session_name)
            ->view('emails.session-reminder')
            ->with([
                'user' => $this->user,
                'settings' => $this->settings,
                'taskData' => $this->sessionDateTime,
                'session' => $this->session,
            ]);
    }
}