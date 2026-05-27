<?php

return [
    'monthly_sales_target' => (int) env('MONTHLY_SALES_TARGET', 10),

  /*
  |--------------------------------------------------------------------------
  | Reverb / Echo — channel publik (tanpa PrivateChannel / tanpa session cookie)
  |--------------------------------------------------------------------------
  */
    'broadcast_channels' => [
        'admin' => 'admin-dashboard',
        'mobile' => 'mobile-app',
    ],

    /*
    | Nama event di WebSocket (tanpa "App\Events\"). Flutter: bind "OfferSubmitted".
    | Laravel Echo (web): listen ".OfferSubmitted" (dengan titik di depan).
    */
    'broadcast_events' => [
        'offer_submitted' => 'OfferSubmitted',
        'offer_cancelled' => 'OfferCancelled',
        'offer_accepted' => 'OfferAccepted',
        'offer_rejected' => 'OfferRejected',
        'vehicle_stock_updated' => 'VehicleStockUpdated',
    ],
];
