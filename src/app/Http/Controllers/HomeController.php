<?php

namespace App\Http\Controllers;

use App\Models\Station;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Station::query()->with(['fuelPrices' => function ($q) {
            $q->orderBy('collected_at', 'desc');
        }]);

        if ($request->filled('city')) {
            $city = strtoupper(trim($request->city));
            $query->where('city', $city);
        }

        if ($request->filled('brand')) {
            $query->where('brand', 'LIKE', '%' . $request->brand . '%');
        }

        $stations = $query->paginate(30)->withQueryString();

        return view('home', compact('stations'));
    }

    public function ethanolGasoline(Request $request)
    {
        return view('calculators.ethanol_gasoline');
    }

    public function detour(Request $request)
    {
        $stations = Station::with(['fuelPrices' => fn($q) => $q->orderBy('collected_at', 'desc')])->get();
        return view('calculators.detour', compact('stations'));
    }

    public function history()
    {
        return view('history');
    }
}
