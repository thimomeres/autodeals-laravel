<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_path' => $this->image_path,
            'image_url' => $this->image_path
                ? asset('storage/' . ltrim($this->image_path, '/'))
                : null,
            'is_primary' => (bool) $this->is_primary,
        ];
    }
}
