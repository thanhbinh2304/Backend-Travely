<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;

class BookingConfirmedNotification extends Notification
{
    use Queueable;

    protected $booking;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
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
            'type' => 'booking_confirmed',
            'title' => 'Booking Confirmed',
            'message' => 'Your booking #' . $this->booking->bookingID . ' has been confirmed.',
            'booking_id' => $this->booking->bookingID,
            'tour_id' => $this->booking->tourID,
            'total_price' => $this->booking->totalPrice,
            'booking_date' => $this->booking->bookingDate,
            'action_url' => '/bookings/' . $this->booking->bookingID,
        ];
    }
}
