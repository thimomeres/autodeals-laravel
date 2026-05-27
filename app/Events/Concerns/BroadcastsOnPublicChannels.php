<?php

namespace App\Events\Concerns;

use Illuminate\Broadcasting\Channel;

trait BroadcastsOnPublicChannels
{
    /**
     * Channel publik — tanpa auth cookie (cocok untuk Flutter + dashboard Blade).
     */
    public function broadcastOn(): array
    {
        return [
            new Channel(config('autodeals.broadcast_channels.admin', 'admin-dashboard')),
            new Channel(config('autodeals.broadcast_channels.mobile', 'mobile-app')),
        ];
    }
}
