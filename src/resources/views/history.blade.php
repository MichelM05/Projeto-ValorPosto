@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-6">Histórico de Preços</h2>

        <div x-data="{
            city: 'CURITIBA',
            fuelType: 'GASOLINA',
            chart: null,

            async fetchData() {
                const response = await fetch(`/api/history?city=${this.city}&fuel_type=${this.fuelType}`);
                const data = await response.json();
                this.updateChart(data);
            },

            updateChart(data) {
                const ctx = document.getElementById('historyChart').getContext('2d');

                if (this.chart) {
                    this.chart.destroy();
                }

                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.map(d => d.collected_at),
                        datasets: [
                            {
                                label: 'Preço Médio (R$)',
                                data: data.map(d => d.average_price),
                                borderColor: 'rgb(59, 130, 246)',
                                tension: 0.1
                            },
                            {
                                label: 'Preço Mínimo (R$)',
                                data: data.map(d => d.min_price),
                                borderColor: 'rgb(34, 197, 94)',
                                tension: 0.1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: false
                            }
                        }
                    }
                });
            }
        }" x-init="fetchData()">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Município</label>
                    <select x-model="city" @change="fetchData()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="CURITIBA">Curitiba</option>
                        <option value="SAO JOSE DOS PINHAIS">São José dos Pinhais</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Combustível</label>
                    <select x-model="fuelType" @change="fetchData()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="GASOLINA">Gasolina</option>
                        <option value="ETANOL">Etanol</option>
                        <option value="DIESEL">Diesel</option>
                    </select>
                </div>
            </div>

            <div class="h-[400px]">
                <canvas id="historyChart"></canvas>
            </div>
        </div>
    </div>

    <div>
        <a href="{{ route('home') }}" class="text-blue-600 hover:underline">&larr; Voltar para a listagem</a>
    </div>
</div>
@endsection
