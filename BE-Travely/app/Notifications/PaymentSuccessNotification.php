<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Checkout;

class PaymentSuccessNotification extends Notification
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
            'type' => 'payment_success',
            'title' => 'Payment Successful',
            'message' => 'Your payment of ' . number_format($this->checkout->amount, 0, ',', '.') . ' VND has been processed successfully.',
            'checkout_id' => $this->checkout->checkoutID,
            'booking_id' => $this->checkout->bookingID,
            'amount' => $this->checkout->amount,
            'payment_method' => $this->checkout->paymentMethod,
            'transaction_id' => $this->checkout->transactionID,
            'action_url' => '/bookings/' . $this->checkout->bookingID,
        ];
    }
}
