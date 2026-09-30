<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FuelPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = FuelPrice::query()
            ->join('stations', 'fuel_prices.station_id', '=', 'stations.id')
            ->select(
                'stations.city',
                'fuel_prices.fuel_type',
                'fuel_prices.collected_at',
                DB::raw('AVG(price) as average_price'),
                DB::raw('MIN(price) as min_price')
            )
            ->groupBy('stations.city', 'fuel_prices.fuel_type', 'fuel_prices.collected_at')
            ->orderBy('fuel_prices.collected_at', 'asc');

        if ($request->has('city')) {
            $query->where('stations.city', strtoupper($request->city));
        }

        if ($request->has('fuel_type')) {
            $query->where('fuel_prices.fuel_type', strtoupper($request->fuel_type));
        }

        return response()->json($query->get());
    }
}
