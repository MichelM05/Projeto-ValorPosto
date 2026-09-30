<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cnpj' => $this->cnpj,
            'name' => $this->name,
            'brand' => $this->brand,
            'address' => $this->address,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'prices' => FuelPriceResource::collection($this->whenLoaded('fuelPrices')),
            'last_prices' => $this->when($this->relationLoaded('fuelPrices'), function() {
                return $this->fuelPrices->groupBy('fuel_type')->map(fn($prices) => new FuelPriceResource($prices->sortByDesc('collected_at')->first()));
            }),
        ];
    }
}
