<?php

namespace App\Notifications;

use App\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewOrderAlert extends Notification
{
    use Queueable;

    public OrderItem $orderItem;

    /**
     * Create a new notification instance.
     */
    public function __construct(OrderItem $orderItem)
    {
        $this->orderItem = $orderItem;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast']; // Store in DB and broadcast in real-time
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_item_id' => $this->orderItem->id,
            'message' => "New {$this->orderItem->service->department} order for patient {$this->orderItem->order->patient->first_name}",
            'link' => $this->getLinkForDepartment($this->orderItem->service->department),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    /**
     * Get the appropriate link based on the department.
     */
    private function getLinkForDepartment(string $department): string
    {
        return match (strtolower($department)) {
            'laboratory' => route('lab.index'),
            'radiology' => route('radiology.index'),
            'pharmacy' => route('pharmacy.index'),
            'procedure' => route('ot.index'),
            default => route('dashboard'),
        };
    }
}
