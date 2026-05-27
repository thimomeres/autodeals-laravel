<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileOfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $car = $this->car;

        return [
            'id' => $this->id,
            'car_id' => $this->car_id,
            'buyer_name' => $this->buyer_name,
            'price_offered' => (float) $this->price_offered,
            'price_offered_formatted' => 'Rp ' . number_format((float) $this->price_offered, 0, ',', '.'),
            'status' => $this->status,
            'status_label' => ucwords(str_replace('_', ' ', $this->status)),
            'vehicle' => [
                'brand' => $car?->brand,
                'model' => $car?->model,
                'stock_code' => $car?->stock_code,
                'status' => $car?->status,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
