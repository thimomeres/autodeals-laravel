<?php

namespace App\Services;

use App\Events\OfferAccepted;
use App\Events\OfferRejected;
use App\Events\VehicleStockUpdated;
use App\Models\Car;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OfferReviewService
{
    public function accept(Offer $offer): Offer
    {
        if ($offer->status !== 'pending_review') {
            throw new InvalidArgumentException('Only pending offers can be accepted.');
        }

        return DB::transaction(function () use ($offer) {
            $offer->update([
                'status' => 'accepted',
                'reject_reason' => null,
            ]);

            $car = Car::find($offer->car_id);
            if ($car) {
                $car->update(['status' => 'sold']);
            }

            $otherPending = Offer::where('car_id', $offer->car_id)
                ->where('id', '!=', $offer->id)
                ->where('status', 'pending_review')
                ->with('car')
                ->get();

            foreach ($otherPending as $other) {
                $other->update([
                    'status' => 'rejected',
                    'reject_reason' => 'Penawaran lain diterima untuk unit ini.',
                ]);
                CustomerNotificationService::notifyOfferRejected(
                    $other->fresh()->load('car'),
                    'Penawaran lain diterima untuk unit ini.',
                );
            }

            $offer->refresh()->load('car');

            CustomerNotificationService::notifyOfferAccepted($offer);

            event(new OfferAccepted($offer));
            if ($car) {
                event(new VehicleStockUpdated($car->fresh(), 'offer_accepted'));
            }

            return $offer;
        });
    }

    public function reject(Offer $offer, ?string $rejectReason = null): Offer
    {
        if ($offer->status !== 'pending_review') {
            throw new InvalidArgumentException('Only pending offers can be rejected.');
        }

        return DB::transaction(function () use ($offer, $rejectReason) {
            $reason = $rejectReason ? trim($rejectReason) : null;

            $offer->update([
                'status' => 'rejected',
                'reject_reason' => $reason,
            ]);

            $remainingPending = Offer::where('car_id', $offer->car_id)
                ->where('id', '!=', $offer->id)
                ->where('status', 'pending_review')
                ->count();

            $car = Car::find($offer->car_id);
            if ($car && $remainingPending === 0 && strtolower((string) $car->status) === 'pending') {
                $car->update(['status' => 'available']);
            }

            $offer->refresh()->load('car');

            CustomerNotificationService::notifyOfferRejected($offer, $reason);

            event(new OfferRejected($offer));
            if ($car && $remainingPending === 0) {
                event(new VehicleStockUpdated($car->fresh(), 'offer_rejected'));
            }

            return $offer;
        });
    }
}
