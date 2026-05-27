<?php

namespace App\Events;

use App\Events\Concerns\BroadcastsOnPublicChannels;
use App\Events\Concerns\UsesCustomBroadcastName;
use App\Models\Car;
use App\Support\BroadcastPayload;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VehicleStockUpdated implements ShouldBroadcastNow
{
    use BroadcastsOnPublicChannels;
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
    use UsesCustomBroadcastName;

    public const BROADCAST_NAME = 'VehicleStockUpdated';

    public function __construct(
        public Car $car,
        public string $reason = 'status_changed',
    ) {}

    public static function broadcastEventName(): string
    {
        return config('autodeals.broadcast_events.vehicle_stock_updated', self::BROADCAST_NAME);
    }

    public function broadcastWith(): array
    {
        return [
            'event_type' => 'vehicle.stock_updated',
            'broadcast_event' => self::broadcastEventName(),
            'channels' => [
                config('autodeals.broadcast_channels.admin', 'admin-dashboard'),
                config('autodeals.broadcast_channels.mobile', 'mobile-app'),
            ],
            'reason' => $this->reason,
            'car' => BroadcastPayload::car($this->car),
            'vehicle' => BroadcastPayload::car($this->car),
            'vehicle_label' => BroadcastPayload::vehicleLabel($this->car),
        ];
    }
}
