<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Log;

class OrderCreated extends Notification
{
    use Queueable;

    private $order_id;
    private $label_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($order_id, $label_id)
    {
        $this->order_id = $order_id;
        $this->label_id = $label_id;
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
        Log::debug("Creating mail to user");
        $data = '';
        $label = \App\Label::where("filename", $this->label_id)->first();
        if ($label)
        {
            $data = base64_decode(stream_get_contents($label->file));
        }
        return (new MailMessage)
                    ->greeting('Congratulations!')
                    ->subject('An Order has been Created')
                    ->line("Order $this->order_id has been created, and a parcel label has been generated.")
                    ->action('View on '.getenv('APP_URL'), url(config('app.url').'/create/'.$this->order_id))
                    ->line('The PDF Label has been attached.')
                    ->attachData($data, "$this->label_id.pdf", ['mime' => 'application/pdf']);
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
