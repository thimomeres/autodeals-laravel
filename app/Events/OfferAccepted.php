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

class OfferAccepted implements ShouldBroadcastNow
{
    use BroadcastsOnPublicChannels;
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
    use UsesCustomBroadcastName;

    public const BROADCAST_NAME = 'OfferAccepted';

    public function __construct(public Offer $offer)
    {
        $this->offer->loadMissing('car');
    }

    public static function broadcastEventName(): string
    {
        return config('autodeals.broadcast_events.offer_accepted', self::BROADCAST_NAME);
    }

    public function broadcastWith(): array
    {
        return BroadcastPayload::offerStatusDecision($this->offer, 'offer.accepted');
    }
}
