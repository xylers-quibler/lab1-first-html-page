<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Product $product)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Low Stock Alert: ' . $this->product->name)
            ->line($this->product->name . ' (' . $this->product->sku . ') is low on stock.')
            ->line('Current quantity: ' . $this->product->quantity)
            ->line('Reorder level: ' . $this->product->reorder_level)
            ->action('View Menu Item', route('products.show', $this->product))
            ->line('Please reorder this item soon.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'name' => $this->product->name,
            'sku' => $this->product->sku,
            'quantity' => $this->product->quantity,
            'reorder_level' => $this->product->reorder_level,
            'message' => $this->product->name . ' is low on stock (' . $this->product->quantity . ' left). Please reorder.',
        ];
    }
}
