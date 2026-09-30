<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Combustível CWB-SJP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        #map { height: 400px; }
    </style>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white p-4 shadow-md">
        <div class="container mx-auto">
            <h1 class="text-2xl font-bold">Combustível CWB-SJP</h1>
        </div>
    </nav>

    <main class="container mx-auto py-6 px-4">
        @yield('content')
    </main>

    <footer class="bg-white border-t mt-12 py-6">
        <div class="container mx-auto text-center text-gray-600 text-sm">
            <p>Fonte de dados: ANP (Agência Nacional do Petróleo, Gás Natural e Biocombustíveis)</p>
            <p>Os valores exibidos são de referência e refletem a data da coleta semanal por amostragem.</p>
            <p class="mt-2">&copy; {{ date('Y') }} - Projeto Portfólio</p>
        </div>
    </footer>
</body>
</html>
