<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPassword extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Restablecer contraseña de SIMRH')
            ->greeting('Hola, '.$notifiable->name)
            ->line('Recibimos una solicitud para restablecer la contraseña de su cuenta en SIMRH.')
            ->action('Crear nueva contraseña', $url)
            ->line('Este enlace vence en '.config('auth.passwords.users.expire', 60).' minutos.')
            ->line('Si no realizó esta solicitud, ignore este mensaje. Su contraseña no cambiará.')
            ->salutation('Saludos, SIMRH');
    }
}
