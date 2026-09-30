@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-6">Calculadora Etanol x Gasolina</h2>

        <div x-data="{
            priceEtanol: '',
            priceGasoline: '',
            consEtanol: '',
            consGasoline: '',
            get result() {
                if (!this.priceEtanol || !this.priceGasoline) return null;
                const ratio = this.priceEtanol / this.priceGasoline;
                let threshold = 0.70;
                if (this.consEtanol && this.consGasoline) {
                    threshold = this.consEtanol / this.consGasoline;
                }
                return {
                    ratio: ratio.toFixed(4),
                    threshold: threshold.toFixed(4),
                    advantageous: ratio < threshold ? 'ETANOL' : 'GASOLINA'
                };
            }
        }">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Preço Etanol (R$)</label>
                    <input type="number" step="0.001" x-model="priceEtanol" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Preço Gasolina (R$)</label>
                    <input type="number" step="0.001" x-model="priceGasoline" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Consumo Etanol (km/l) - Opcional</label>
                    <input type="number" step="0.1" x-model="consEtanol" placeholder="Ex: 8.5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Consumo Gasolina (km/l) - Opcional</label>
                    <input type="number" step="0.1" x-model="consGasoline" placeholder="Ex: 12.0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <template x-if="result">
                <div :class="result.advantageous === 'ETANOL' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-blue-100 border-blue-500 text-blue-700'" class="p-4 border-l-4 rounded">
                    <p class="font-bold text-lg">Abasteça com <span x-text="result.advantageous"></span>!</p>
                    <p class="text-sm mt-1">
                        Relação de preço: <span x-text="result.ratio"></span> |
                        Limite (Break-even): <span x-text="result.threshold"></span>
                    </p>
                    <p x-show="!consEtanol || !consGasoline" class="text-xs mt-2 opacity-75">* Calculado usando a média padrão de 70%.</p>
                </div>
            </template>
        </div>

        <div class="mt-8">
            <a href="{{ route('home') }}" class="text-blue-600 hover:underline">&larr; Voltar para a listagem</a>
        </div>
    </div>
</div>
@endsection
