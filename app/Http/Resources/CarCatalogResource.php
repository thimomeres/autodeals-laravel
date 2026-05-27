<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarCatalogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $primaryImage = $this->images->firstWhere('is_primary', true)
            ?? $this->images->first();

        return [
            'id' => $this->id,
            'stock_code' => $this->stock_code,
            'brand' => $this->brand,
            'model' => $this->model,
            'year' => $this->year,
            'price' => (float) $this->price,
            'price_formatted' => 'Rp ' . number_format((float) $this->price, 0, ',', '.'),
            'mileage' => $this->mileage,
            'color' => $this->color,
            'transmission' => $this->transmission,
            'fuel_type' => $this->fuel_type,
            'condition' => $this->condition,
            'status' => $this->status,
            'thumbnail_url' => $primaryImage?->image_path
                ? asset('storage/' . ltrim($primaryImage->image_path, '/'))
                : null,
            'images' => CarImageResource::collection($this->whenLoaded('images')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
