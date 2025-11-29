<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Checkout;

class RefundProcessedNotification extends Notification
{
    use Queueable;

    protected $checkout;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Checkout $checkout)
    {
        $this->checkout = $checkout;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
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
            'type' => 'refund_processed',
            'title' => 'Refund Processed',
            'message' => 'Your refund of ' . number_format($this->checkout->refundAmount, 0, ',', '.') . ' VND has been processed.',
            'checkout_id' => $this->checkout->checkoutID,
            'booking_id' => $this->checkout->bookingID,
            'refund_amount' => $this->checkout->refundAmount,
            'refund_reason' => $this->checkout->refundReason,
            'refund_date' => $this->checkout->refundDate,
            'action_url' => '/bookings/' . $this->checkout->bookingID,
        ];
    }
}
