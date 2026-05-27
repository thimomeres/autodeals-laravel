<?php

namespace App\Services;

use App\Models\CustomerNotification;
use App\Models\Offer;
use App\Support\BroadcastPayload;

class CustomerNotificationService
{
    public static function notifyOfferAccepted(Offer $offer): void
    {
        if (! $offer->customer_id) {
            return;
        }

        $label = BroadcastPayload::vehicleLabel($offer->car);

        self::create(
            (int) $offer->customer_id,
            'Penawaran Diterima 🎉',
            "Penawaran Anda untuk {$label} telah disetujui!",
        );
    }

    public static function notifyOfferRejected(Offer $offer, ?string $reason = null): void
    {
        if (! $offer->customer_id) {
            return;
        }

        $label = BroadcastPayload::vehicleLabel($offer->car);
        $message = "Penawaran Anda untuk {$label} ditolak.";

        if ($reason) {
            $message .= ' Alasan: ' . $reason;
        }

        self::create(
            (int) $offer->customer_id,
            'Penawaran Ditolak',
            $message,
        );
    }

    public static function create(int $customerId, string $title, string $message): CustomerNotification
    {
        return CustomerNotification::create([
            'customer_id' => $customerId,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);
    }
}
