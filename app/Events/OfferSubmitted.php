<?php

namespace App\Events;

use App\Models\Offer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Offer $offer)
    {
        $this->offer->loadMissing('car');
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('admin-dashboard'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OfferSubmitted';
    }

    public function broadcastWith(): array
    {
        $car = $this->offer->car;

        return [
            'offer' => [
                'id' => $this->offer->id,
                'car_id' => $this->offer->car_id,
                'buyer_name' => $this->offer->buyer_name,
                'price_offered' => $this->offer->price_offered,
                'price_offered_formatted' => number_format((float) $this->offer->price_offered, 0, ',', '.'),
                'status' => $this->offer->status,
                'created_at' => $this->offer->created_at?->toIso8601String(),
            ],
            'buyer_name' => $this->offer->buyer_name,
            'price_offered' => $this->offer->price_offered,
            'price_offered_formatted' => 'Rp ' . number_format((float) $this->offer->price_offered, 0, ',', '.'),
            'car' => [
                'brand' => $car?->brand,
                'model' => $car?->model,
                'stock_code' => $car?->stock_code,
            ],
            'pending_review_count' => Offer::where('status', 'pending_review')->count(),
        ];
    }
}
