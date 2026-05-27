<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Services\ActivityLogger;
use App\Services\OfferReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class OfferReviewController extends Controller
{
    public function __construct(
        private readonly OfferReviewService $offerReview,
    ) {}

    /**
     * POST /offers/{offer}/accept — setujui penawaran (admin web).
     */
    public function accept(Offer $offer): RedirectResponse
    {
        try {
            $offer = $this->offerReview->accept($offer);
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        ActivityLogger::log(
            'offer.accepted',
            "Penawaran diterima dari {$offer->buyer_name}",
            Offer::class,
            $offer->id,
        );

        return redirect()->back()->with(
            'success',
            'Offer accepted! The vehicle is now marked as SOLD.',
        );
    }

    /**
     * POST /offers/{offer}/reject — tolak penawaran + alasan (admin web).
     */
    public function reject(Request $request, Offer $offer): RedirectResponse
    {
        $validated = $request->validate([
            'reject_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $offer = $this->offerReview->reject($offer, $validated['reject_reason'] ?? null);
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        ActivityLogger::log(
            'offer.rejected',
            "Penawaran ditolak dari {$offer->buyer_name}",
            Offer::class,
            $offer->id,
            ['reject_reason' => $offer->reject_reason],
        );

        return redirect()->back()->with('success', 'Offer has been rejected successfully.');
    }
}
