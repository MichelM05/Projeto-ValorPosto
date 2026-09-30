# Fuel CWB-SJP (Combustível Curitiba & São José dos Pinhais)

A PHP/Laravel application to help drivers in Curitiba and São José dos Pinhais (PR, Brazil) decide where and with what fuel to refill.

## Features

- **Price Listing & Map**: View the latest fuel prices from ANP (National Petroleum Agency) on a list or interactive map (OpenStreetMap).
- **Detour Calculator**: "Is the detour worth it?" - Calculates the net saving considering distance, car consumption, and fuel price.
- **Ethanol vs. Gasoline Calculator**: Compares which fuel is more advantageous based on the car's specific consumption or the 70% standard.
- **Price History**: Weekly charts showing average and minimum prices per city and fuel type.
- **Public API**: Read-only JSON API for stations, prices, and history.

## Tech Stack

- **Backend**: PHP 8.3+, Laravel 11, PostgreSQL 16.
- **Frontend**: Blade, Tailwind CSS, Alpine.js, Leaflet (Maps), Chart.js (Graphics).
- **Tooling**: Docker Compose, PHPUnit/Pest, Larastan (Static Analysis), Laravel Pint (Linting).
- **Geocoding**: Nominatim (OpenStreetMap) with caching and rate limiting.
- **CI**: GitHub Actions for testing and linting.

## Requirements

- Docker and Docker Compose.

## Getting Started

1. Clone the repository.
2. Run the setup command:
   ```bash
   docker-compose up -d --build
   ```
3. Install dependencies, run migrations and seed data (inside the app container):
   ```bash
   docker exec -it combustivel-cwb-sjp-app composer install --ignore-platform-reqs
   docker exec -it combustivel-cwb-sjp-app php artisan key:generate
   docker exec -it combustivel-cwb-sjp-app php artisan migrate --seed
   ```
4. (Optional) Run the real ANP import (replaces seed data):
   ```bash
   docker exec -it combustivel-cwb-sjp-app php artisan combustivel:import "PATH_OR_URL_TO_XLSX"
   ```
5. Access the application at `http://localhost:8080`.

## Data Source

This project uses open data from **ANP (Agência Nacional do Petróleo, Gás Natural e Biocombustíveis)**.
- **URL**: The data is available at the [ANP Prices Page](https://www.gov.br/anp/pt-br/centrais-de-conteudo/dados-abertos/levantamento-de-precos-de-combustiveis-ultimas-semanas-pesquisadas).
- **Frequency**: Prices are updated **weekly**. The files are usually released in XLSX format, covering the national territory.
- **Note**: The importer filters only Curitiba and São José dos Pinhais, and vehicular fuels (Gasoline, Ethanol, Diesel S10, NGV). GLP is discarded.

## Architecture Decisions

- **Streaming Import**: Uses `OpenSpout` to read large XLSX files in streaming mode, ensuring low memory usage.
- **Idempotent Import**: The importer ensures that re-importing the same file or data for the same station/date doesn't duplicate records.
- **Geocoding Cache**: Station coordinates are cached to respect Nominatim's usage policy and improve performance.
- **Calculations**: Business logic is isolated in pure Service classes (`CalculationService`) with high test coverage.
- **Privacy**: User location and car data are processed only in the browser (Alpine.js) and never persisted.

## API Endpoints

- `GET /api/stations`: List stations with pagination and filters.
- `GET /api/stations/{id}`: Detail of a specific station.
- `GET /api/history`: Price history data for charts.

## License

MIT
