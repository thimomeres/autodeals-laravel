<?php

namespace App\Events\Concerns;

/**
 * Memastikan Reverb/Pusher mengirim nama event bersih (mis. "OfferSubmitted"),
 * bukan FQCN "App\Events\OfferSubmitted". Flutter bind ke nama yang sama;
 * Laravel Echo di web memakai prefix titik: ".OfferSubmitted".
 */
trait UsesCustomBroadcastName
{
    /**
     * Nama event di wire — tanpa namespace App\Events\.
     */
    abstract public static function broadcastEventName(): string;

    public function broadcastAs(): string
    {
        return static::broadcastEventName();
    }
}
