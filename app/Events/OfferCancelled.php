<?php

namespace App\Events;

use App\Events\Concerns\BroadcastsOnPublicChannels;
use App\Events\Concerns\UsesCustomBroadcastName;
use App\Models\Offer;
use App\Support\BroadcastPayload;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferCancelled implements ShouldBroadcastNow
{
    use BroadcastsOnPublicChannels;
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
    use UsesCustomBroadcastName;

    public const BROADCAST_NAME = 'OfferCancelled';

    public function __construct(public Offer $offer, public string $vehicleLabel)
    {
        $this->offer->loadMissing('car');
    }

    public static function broadcastEventName(): string
    {
        return config('autodeals.broadcast_events.offer_cancelled', self::BROADCAST_NAME);
    }

    public function broadcastWith(): array
    {
        $car = $this->offer->car;
        $buyerName = $this->offer->buyer_name;

        return [
            'event_type' => 'offer.cancelled',
            'broadcast_event' => self::broadcastEventName(),
            'channels' => [
                config('autodeals.broadcast_channels.admin', 'admin-dashboard'),
                config('autodeals.broadcast_channels.mobile', 'mobile-app'),
            ],
            'offer' => BroadcastPayload::offer($this->offer),
            'buyer_name' => $buyerName,
            'price_offered' => (float) $this->offer->price_offered,
            'vehicle_label' => $this->vehicleLabel,
            'car' => BroadcastPayload::car($car),
            'vehicle' => BroadcastPayload::car($car),
            'cancel_message' => "Pemberitahuan: Penawaran dari {$buyerName} untuk mobil {$this->vehicleLabel} telah DIBATALKAN oleh pengguna.",
            'pending_review_count' => Offer::where('status', 'pending_review')->count(),
        ];
    }
}
