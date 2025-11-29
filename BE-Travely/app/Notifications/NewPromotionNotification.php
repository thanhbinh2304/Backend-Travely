<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Promotion;

class NewPromotionNotification extends Notification
{
    use Queueable;

    protected $promotion;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Promotion $promotion)
    {
        $this->promotion = $promotion;
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
            'type' => 'new_promotion',
            'title' => 'New Promotion Available',
            'message' => $this->promotion->description . ' - Get ' . $this->promotion->discount . '% off!',
            'promotion_id' => $this->promotion->promotionID,
            'discount' => $this->promotion->discount,
            'start_date' => $this->promotion->startDate,
            'end_date' => $this->promotion->endDate,
            'quantity' => $this->promotion->quantity,
            'action_url' => '/promotions/' . $this->promotion->promotionID,
        ];
    }
}
