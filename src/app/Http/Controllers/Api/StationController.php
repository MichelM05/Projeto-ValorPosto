<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StationResource;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Station::query()->with(['fuelPrices' => function($q) {
            $q->orderBy('collected_at', 'desc');
        }]);

        if ($request->has('city')) {
            $query->where('city', strtoupper($request->city));
        }

        if ($request->has('fuel_type')) {
            $query->whereHas('fuelPrices', function($q) use ($request) {
                $q->where('fuel_type', strtoupper($request->fuel_type));
            });
        }

        $stations = $query->paginate(50);

        return StationResource::collection($stations);
    }

    public function show(Station $station): StationResource
    {
        $station->load(['fuelPrices' => function($q) {
            $q->orderBy('collected_at', 'desc');
        }]);

        return new StationResource($station);
    }
}
