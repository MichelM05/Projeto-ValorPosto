@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Filtros</h2>
            <form action="{{ route('home') }}" method="GET" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Município</label>
                    <select name="city" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="CURITIBA" {{ request('city') == 'CURITIBA' ? 'selected' : '' }}>Curitiba</option>
                        <option value="SAO JOSE DOS PINHAIS" {{ request('city') == 'SAO JOSE DOS PINHAIS' ? 'selected' : '' }}>São José dos Pinhais</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Bandeira</label>
                    <input type="text" name="brand" value="{{ request('brand') }}" placeholder="Ex: BR, Shell..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Filtrar</button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Acesso Rápido</h2>
            <div class="space-y-2">
                <a href="{{ route('history') }}" class="block p-3 bg-gray-50 rounded hover:bg-gray-100 transition">Histórico de Preços</a>
                <a href="{{ route('calc.ethanol_gasoline') }}" class="block p-3 bg-gray-50 rounded hover:bg-gray-100 transition">Etanol x Gasolina</a>
                <a href="{{ route('calc.detour') }}" class="block p-3 bg-gray-50 rounded hover:bg-gray-100 transition">Vale a pena o desvio?</a>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b flex justify-between items-center">
                <h2 class="text-xl font-semibold">Catálogo de Postos e Preços</h2>
                <button onclick="document.getElementById('map-container').classList.toggle('hidden')" class="text-sm text-blue-600 hover:underline">
                    Ver/Esconder Mapa
                </button>
            </div>

            <div id="map-container" class="hidden border-b">
                <div id="map" class="w-full h-96"></div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-700 text-sm uppercase">
                        <tr>
                            <th class="px-6 py-3">Posto</th>
                            <th class="px-4 py-3 text-center">Gasolina</th>
                            <th class="px-4 py-3 text-center">Etanol</th>
                            <th class="px-4 py-3 text-center">Diesel</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($stations as $station)
                            @php
                                $prices = $station->fuelPrices->groupBy('fuel_type')->map->first();
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $station->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $station->brand }}</div>
                                    <div class="text-[10px] text-gray-400 mt-1">
                                        {{ $station->address }}, {{ $station->neighborhood }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if(isset($prices['GASOLINA']))
                                        <div class="font-bold text-blue-600">R$ {{ number_format($prices['GASOLINA']->price, 3, ',', '.') }}</div>
                                        <div class="text-[9px] text-gray-400">{{ $prices['GASOLINA']->collected_at->diffForHumans() }}</div>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if(isset($prices['ETANOL']))
                                        <div class="font-bold text-green-600">R$ {{ number_format($prices['ETANOL']->price, 3, ',', '.') }}</div>
                                        <div class="text-[9px] text-gray-400">{{ $prices['ETANOL']->collected_at->diffForHumans() }}</div>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if(isset($prices['DIESEL']))
                                        <div class="font-bold text-orange-600">R$ {{ number_format($prices['DIESEL']->price, 3, ',', '.') }}</div>
                                        <div class="text-[9px] text-gray-400">{{ $prices['DIESEL']->collected_at->diffForHumans() }}</div>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center">
                                    <div class="text-gray-500 mb-2">Nenhum posto encontrado.</div>
                                    <div class="text-sm text-blue-600">
                                        Dica: Se você acabou de instalar, execute a importação dos dados da ANP via Docker.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $stations->links() }}
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = L.map('map').setView([-25.4284, -49.2733], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        const stations = @json($stations->items());
        let hasMarkers = false;
        stations.forEach(station => {
            if (station.latitude && station.longitude) {
                L.marker([station.latitude, station.longitude])
                    .addTo(map)
                    .bindPopup(`<b>${station.name}</b><br>${station.address}`);
                hasMarkers = true;
            }
        });

        if (!hasMarkers && stations.length > 0) {
            // Se nenhum posto tem coordenadas na página atual, foca no centro da cidade selecionada
            const city = "{{ request('city') }}";
            if (city === 'SAO JOSE DOS PINHAIS') {
                map.setView([-25.5348, -49.2064], 13);
            }
        }
    });
</script>
@endsection
