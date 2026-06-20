---
tipo: estado-actual
descripcion: Stack y versiones reales, verificadas en composer.json y package.json
actualizado: 2026-06-20
---

# Stack tecnológico

Foto verificada en `composer.json` y `package.json` (no en el README, que está
desactualizado — ver [[brechas-conocidas]]).

## Backend (`composer.json`)
- **PHP** `^8.2`
- **Laravel** `^12.0`
- **Livewire**: Flux `^2.1`, Volt `^1.7` (componentes de clase en `app/Livewire/`)
- **Laravel Sanctum** `^4.0` (tokens API)
- **maatwebsite/excel** `^3.1` — importación/exportación Excel
- **setasign/fpdi** `^2.6` + **setasign/tfpdf** `^1.33` — generación de PDF de asistencia
- **simplesoftwareio/simple-qrcode** `^4.2` — QR de acceso al evento
- Dev: `laravel-lang/common`, `laravel/pint`, `phpunit/phpunit ^11.5`, `laravel/pail`

## Frontend (`package.json`, v1.2.1)
- **Vite** `^7.0`
- **Tailwind CSS v4** (`@tailwindcss/vite`) — sin `tailwind.config` clásico
- **Alpine.js** `^3.15` — interactividad ligera en Blade
- **React** `^19.2` + **react-dom** + **Recharts** `^3.7` + `@vitejs/plugin-react`
  → patrón **React Islands** para estadísticas (ver [[arquitectura]] y [[adr-0001-react-islands-estadisticas]])
- **axios** `^1.7`

## Base de datos
- **SQLite** en local (`database/database.sqlite`)
- **MySQL** en producción (Render)
- `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`

## Internacionalización
- Locale por defecto `es` / `es_CO` (`laravel-lang`), middleware `SetLocale`.

## Despliegue
- Producción en **Render**: `https://asistencia-uniguajira.onrender.com/`

## Comandos clave
- `composer run dev` — server + queue + vite a la vez
- `php artisan migrate --seed` — migra y siembra (siembra parcial, ver [[brechas-conocidas]])
- `composer run test` / `php artisan test`
- `./vendor/bin/pint` — linter PHP

> Las convenciones de código vinculantes están en `CLAUDE.md`; aquí solo se resumen en [[convenciones]].
