# Fuel CWB-SJP (Combustível Curitiba & São José dos Pinhais)

A Laravel application that helps drivers in **Curitiba** and **São José dos Pinhais** (PR, Brazil) decide where, and with which fuel, to refuel. It lists every registered gas station with its latest ANP price, and ships two calculators: *is the detour worth it?* and *ethanol vs. gasoline*.

The UI is in Portuguese (pt-BR); code and documentation are in English.

## Features

- **Station catalogue** with the latest price per fuel (gasoline, ethanol/"álcool", diesel), city and brand filters, price ranking by clicking a column, Google Maps links and a coverage indicator. Every price shows its collection date; prices older than 4 weeks carry a "preço de há X semanas" badge and stay out of rankings and calculators.
- **Nearby stations**: browser geolocation sorts stations by distance (the location is never stored).
- **Detour calculator**: net saving = (reference price − station price) × liters − detour cost; straight-line distance × a configurable correction factor (default 1.3).
- **Ethanol vs. gasoline calculator**: compares the price ratio with the consumption ratio (70% rule when consumption is unknown).
- **Price history** chart (average and minimum price per city/fuel).
- **Read-only JSON API**: `/api/stations`, `/api/stations/{id}`, `/api/history`.

## How the data gets in (the short version)

| Source | Gives us | Command |
| --- | --- | --- |
| ANP registry API (CSV as fallback) | **All** stations (name, address, CEP, brand) **and coordinates** | `combustivel:import-stations` |
| ANP weekly price XLSX | Prices — a **weekly sample**, not every station | `combustivel:import` |
| Nominatim (OpenStreetMap) | Coordinates only for stations ANP has none for | `combustivel:geocode` |

The registry is the source of truth for stations; the price sheet only attaches prices (and creates a station only if the registry doesn't know the CNPJ yet). A station with no price this week still appears in the list — "no price" never means "closed".

Details: [`src/docs/architecture.md`](src/docs/architecture.md), [`src/docs/import-flow.md`](src/docs/import-flow.md), [`src/docs/data-sources.md`](src/docs/data-sources.md), [`src/docs/development.md`](src/docs/development.md).

## Quick start

```bash
docker-compose up -d --build
docker exec -it combustivel-cwb-sjp-app composer install
docker exec -it combustivel-cwb-sjp-app php artisan key:generate
docker exec -it combustivel-cwb-sjp-app php artisan migrate

# 1. Station registry + coordinates, from the ANP API (add a CSV path to use the CSV instead)
docker exec -it combustivel-cwb-sjp-app php artisan combustivel:import-stations
# 2. Weekly prices: one file, several files, a directory of weekly sheets (oldest week first) or a URL
docker exec -it combustivel-cwb-sjp-app php artisan combustivel:import storage/app/precos
# 3. Coordinates for the few stations ANP has none for (1 request/second)
docker exec -it combustivel-cwb-sjp-app php artisan combustivel:geocode --limit=50
```

Open <http://localhost:8080>. The scheduler container re-imports prices every Monday at 02:00 and geocodes up to 100 new stations daily at 03:00.

> The default `.env` uses SQLite (`database/database.sqlite`). The Compose file also starts PostgreSQL 16; point `DB_*` at it to use it.

## Tests

```bash
docker exec combustivel-cwb-sjp-app php artisan test
```

Tests run on an in-memory SQLite database (see `phpunit.xml`), so they never touch your data.

## Stack

PHP 8.3+, Laravel 11, Blade + Tailwind (CDN) + Alpine.js + Chart.js, OpenSpout (streaming XLSX), Pest/PHPUnit, Docker Compose (app, nginx, postgres, scheduler).

## Data limitations

- Prices come from ANP's **weekly sampling**: on any given week many stations have no price. Importing several weeks widens coverage (a station's newest price within 12 weeks is shown, flagged by age), but ANP does not guarantee every station is sampled. `php artisan combustivel:coverage` shows the real numbers.
- ANP's registry API carries coordinates (field accuracy varies, and ANP marks them "not validated"); the few missing or out-of-region ones are geocoded from the address (falling back to the CEP). The precision is shown in the UI.
- Only Curitiba and São José dos Pinhais are imported (`ANP_ALLOWED_CITIES`). *Pinhais* is a different city and is excluded.
- GLP (cooking gas) and non-vehicular products are discarded.

## Data source and license

Open data from ANP (Agência Nacional do Petróleo, Gás Natural e Biocombustíveis). This is a technical demonstration; values are reference prices.

MIT License.
