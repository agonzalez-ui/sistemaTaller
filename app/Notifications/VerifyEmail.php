<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends Notification
{
    use Queueable;

    

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * cuerpo del contenido del email
     */
    public function toMail(object $notifiable): MailMessage
    {
            $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        return (new MailMessage)
            ->subject('Confirma tu cuenta en SIMRH')
            ->greeting('Hola!')
            ->line('Gracias por registrarte en SIMRH, tu cuenta ya esta lista solo debes confirmarla')
            ->action('Confirmar Cuenta', $verificationUrl)
            ->line('Si no creaste esta cuenta, puedes ignorar este mensaje.')
            ->salutation('Saludos, SIMRH');
            
    }


}
