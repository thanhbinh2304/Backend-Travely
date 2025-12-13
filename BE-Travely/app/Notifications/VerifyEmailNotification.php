<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    public $verificationUrl;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($verificationUrl)
    {
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Xác thực địa chỉ email - Travely')
            ->greeting('Xin chào!')
            ->line('Cảm ơn bạn đã đăng ký tài khoản tại Travely.')
            ->line('Vui lòng nhấn vào nút bên dưới để xác thực địa chỉ email của bạn.')
            ->action('Xác thực Email', $this->verificationUrl)
            ->line('Link xác thực sẽ hết hạn sau 24 giờ.')
            ->line('Nếu bạn không tạo tài khoản này, vui lòng bỏ qua email này.')
            ->salutation('Trân trọng, Đội ngũ Travely');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
