<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FuelPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'fuel_type' => $this->fuel_type,
            'price' => (float) $this->price,
            'collected_at' => $this->collected_at->format('Y-m-d'),
        ];
    }
}
