<?php

namespace App\Support;

use App\Models\Car;
use App\Models\Offer;

class BroadcastPayload
{
    public static function car(?Car $car): array
    {
        if (! $car) {
            return [];
        }

        return [
            'id' => $car->id,
            'stock_code' => $car->stock_code,
            'brand' => $car->brand,
            'model' => $car->model,
            'year' => $car->year,
            'price' => (float) $car->price,
            'price_formatted' => 'Rp ' . number_format((float) $car->price, 0, ',', '.'),
            'mileage' => $car->mileage,
            'color' => $car->color,
            'transmission' => $car->transmission,
            'fuel_type' => $car->fuel_type,
            'condition' => $car->condition,
            'status' => $car->status,
            'is_available' => strtolower((string) $car->status) === 'available',
        ];
    }

    public static function offer(Offer $offer): array
    {
        return [
            'id' => $offer->id,
            'car_id' => $offer->car_id,
            'customer_id' => $offer->customer_id,
            'buyer_name' => $offer->buyer_name,
            'price_offered' => (float) $offer->price_offered,
            'price_offered_formatted' => 'Rp ' . number_format((float) $offer->price_offered, 0, ',', '.'),
            'status' => $offer->status,
            'status_label' => ucwords(str_replace('_', ' ', (string) $offer->status)),
            'reject_reason' => $offer->reject_reason,
            'created_at' => $offer->created_at?->toIso8601String(),
            'updated_at' => $offer->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Payload WebSocket keputusan admin (accept / reject) untuk Flutter.
     *
     * @return array<string, mixed>
     */
    public static function offerStatusDecision(Offer $offer, string $eventType): array
    {
        $car = $offer->car;

        return [
            'event_type' => $eventType,
            'broadcast_event' => $eventType === 'offer.accepted'
                ? config('autodeals.broadcast_events.offer_accepted', 'OfferAccepted')
                : config('autodeals.broadcast_events.offer_rejected', 'OfferRejected'),
            'channels' => [
                config('autodeals.broadcast_channels.admin', 'admin-dashboard'),
                config('autodeals.broadcast_channels.mobile', 'mobile-app'),
            ],
            'offer_id' => $offer->id,
            'customer_id' => $offer->customer_id,
            'status' => $offer->status,
            'status_label' => ucwords(str_replace('_', ' ', (string) $offer->status)),
            'vehicle_label' => self::vehicleLabel($car),
            'reject_reason' => $offer->reject_reason,
            'buyer_name' => $offer->buyer_name,
            'offer' => self::offer($offer),
            'car' => self::car($car),
            'vehicle' => self::car($car),
            'pending_review_count' => Offer::where('status', 'pending_review')->count(),
        ];
    }

    public static function vehicleLabel(?Car $car): string
    {
        if (! $car) {
            return 'Mobil';
        }

        return trim($car->brand . ' ' . $car->model) ?: 'Mobil';
    }

    /**
     * Payload seragam untuk WebSocket + polling sync dashboard admin.
     *
     * @return array<string, mixed>
     */
    public static function offerSubmittedEnvelope(Offer $offer): array
    {
        $car = $offer->car;

        return [
            'event_type' => 'offer.submitted',
            'offer' => self::offer($offer),
            'buyer_name' => $offer->buyer_name,
            'price_offered' => (float) $offer->price_offered,
            'price_offered_formatted' => 'Rp ' . number_format((float) $offer->price_offered, 0, ',', '.'),
            'car' => self::car($car),
            'vehicle' => self::car($car),
            'vehicle_label' => self::vehicleLabel($car),
            'pending_review_count' => Offer::where('status', 'pending_review')->count(),
        ];
    }
}
