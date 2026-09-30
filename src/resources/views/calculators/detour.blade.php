@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-6">Vale a pena o desvio?</h2>

        <div x-data="{
            consumption: 10,
            liters: 40,
            refPrice: '',
            stations: @json($stations),
            userLat: null,
            userLon: null,
            results: [],

            calculate() {
                if (!this.refPrice || !this.userLat) return;

                this.results = this.stations.map(s => {
                    if (!s.latitude || !s.longitude) return null;

                    const dist = this.getDistance(this.userLat, this.userLon, s.latitude, s.longitude) * 1.3;
                    const latestPrice = s.fuel_prices[0]?.price;
                    if (!latestPrice) return null;

                    const priceDiff = this.refPrice - latestPrice;
                    const grossSaving = priceDiff * this.liters;
                    const detourCost = (dist / this.consumption) * latestPrice;
                    const netSaving = grossSaving - detourCost;

                    return {
                        name: s.name,
                        distance: dist.toFixed(2),
                        gross: grossSaving.toFixed(2),
                        cost: detourCost.toFixed(2),
                        net: netSaving.toFixed(2)
                    };
                }).filter(r => r !== null).sort((a, b) => b.net - a.net);
            },

            getDistance(lat1, lon1, lat2, lon2) {
                const R = 6371;
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                          Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                          Math.sin(dLon/2) * Math.sin(dLon/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                return R * c;
            },

            getLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(pos => {
                        this.userLat = pos.coords.latitude;
                        this.userLon = pos.coords.longitude;
                        this.calculate();
                    });
                }
            }
        }" x-init="getLocation()">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 bg-gray-50 p-4 rounded border">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Consumo Carro (km/l)</label>
                    <input type="number" x-model="consumption" @input="calculate()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Litros a abastecer</label>
                    <input type="number" x-model="liters" @input="calculate()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Preço ref. (Posto perto)</label>
                    <input type="number" step="0.001" x-model="refPrice" @input="calculate()" placeholder="Ex: 5.69" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>

            <div x-show="!userLat" class="mb-4 text-orange-600 text-sm italic">
                Aguardando geolocalização do navegador para calcular distâncias...
            </div>

            <div x-show="results.length > 0">
                <h3 class="font-bold text-lg mb-4 text-gray-700">Resultados (Ordenados por economia líquida)</h3>
                <div class="space-y-4">
                    <template x-for="res in results.slice(0, 10)" :key="res.name">
                        <div :class="res.net > 0 ? 'border-green-300 bg-green-50' : 'border-red-200 bg-red-50'" class="p-4 border rounded shadow-sm flex justify-between items-center">
                            <div>
                                <p class="font-bold text-gray-800" x-text="res.name"></p>
                                <p class="text-xs text-gray-500">Distância estimada: <span x-text="res.distance"></span> km</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm">Economia bruta: R$ <span x-text="res.gross"></span></p>
                                <p class="text-sm">Custo desvio: R$ <span x-text="res.cost"></span></p>
                                <p class="font-bold text-lg" :class="res.net > 0 ? 'text-green-700' : 'text-red-700'">
                                    Saldo: R$ <span x-text="res.net"></span>
                                </p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="mt-8 border-t pt-6">
            <a href="{{ route('home') }}" class="text-blue-600 hover:underline">&larr; Voltar para a listagem</a>
        </div>
    </div>
</div>
@endsection
