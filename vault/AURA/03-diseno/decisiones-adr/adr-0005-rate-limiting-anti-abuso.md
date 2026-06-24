---
tipo: adr
descripcion: ADR-0005 (propuesta) — Rate limiting / throttling de rutas para mitigar abuso y DDoS
actualizado: 2026-06-20
---

# ADR-0005 · Rate limiting anti-abuso en rutas

- **Estado:** 🟢 Implementado
- **Fecha:** 2026-06-20
- **Implementado:** 2026-06-21
- **Contexto del repo:** `routes/web.php` (rutas públicas `events.access`, `attendance.store`,
  `attendance.confirmation`), `routes/api.php` (estadísticas, varias **sin auth** — ver
  [[brechas-conocidas]] #4), `bootstrap/app.php` (registro de middleware en Laravel 12).

## Contexto
Hay rutas **públicas** que cualquiera puede invocar repetidamente: el registro de asistencia por
QR (`POST /events/acceso/{slug}`), la página de acceso, y los endpoints de estadística sin
autenticación. Hoy no hay límites de tasa explícitos, lo que abre la puerta a fuerza bruta sobre
documentos, scraping de agregados y saturación por peticiones masivas.

## Decisión propuesta
Definir **limitadores con nombre** (`RateLimiter::for(...)`, en un service provider o
`bootstrap/app.php`) y aplicarlos con el middleware `throttle:`:

- `attendance` — registro público por QR: límite por **IP + slug** (p.ej. N intentos/min) para
  frenar fuerza bruta de documentos.
- `public` — páginas públicas de acceso: límite holgado por IP.
- `api-stats` — endpoints `/api/statistics/*`: límite por IP (complementa, no sustituye, la
  decisión de autenticarlos de [[brechas-conocidas]] #4).
- `auth` — login: confirmar/afinar el throttle del starter kit.
- `export`/`pdf` — descargas pesadas (PDF de asistencia): límite por usuario.

Devolver `429 Too Many Requests` con cabeceras `Retry-After`.

## Consecuencias
- ➕ Mitiga fuerza bruta, scraping y picos de abuso **a nivel de aplicación**.
- ➕ Barato de implementar (middleware nativo de Laravel).
- ➖ **No** es una defensa real contra DDoS volumétrico: eso se mitiga en el **borde** (CDN/WAF
  tipo Cloudflare, reglas del proveedor). Documentar esta limitación; el rate limit de app es la
  capa de aplicación, no la de red.
- ➖ Riesgo de falsos positivos detrás de NAT/proxy (varios usuarios, una IP) → afinar claves y
  límites; considerar `X-Forwarded-For` correcto en Hostinger.
- 🔁 Toca la definición de rutas (contratos de respuesta: ahora pueden devolver 429).

## Alternativas consideradas
- **Solo defensa de borde (Cloudflare)**: ideal pero externo; conviene tenerla además, no en vez
  del límite de aplicación.
- **No hacer nada**: deja expuestas las rutas públicas.

## Implementación

- **Limitadores con nombre** en [`AppServiceProvider::configureRateLimiters()`](../../../../app/Providers/AppServiceProvider.php);
  los valores viven en [`config/throttle.php`](../../../../config/throttle.php) y son ajustables vía `.env`
  (`THROTTLE_ATTENDANCE`, `THROTTLE_PUBLIC`, `THROTTLE_API_STATS`, `THROTTLE_PDF`). El cierre lee
  `config()` por petición → se puede afinar/probar sin recompilar.
- **Rama:** `feat/rate-limiting-rutas`.
- **Tests:** `tests/Feature/RateLimitingTest.php` (429 + `Retry-After`; el cupo de asistencia no se
  comparte entre slugs distintos).

### Límites por defecto y dónde se aplican

| Limitador | Defecto | Clave | Rutas |
|---|---|---|---|
| `attendance` | 30/min | IP + slug | ⚠️ **huérfano** — su única ruta (`attendance.store`) se retiró en [[adr-0003-retirar-flujo-legacy-de-asistencia]] |
| `public` | 60/min | IP | `events.access` (`attendance.confirmation` también se retiró en ADR-0003) |
| `api-stats` | 300/min | usuario (o IP) | grupos `/api/statistics/*` (resumen, generales, admin-eventos, compare) |
| `pdf` | 10/min | usuario (o IP) | `events.download` (PDF de asistencia) |

Devuelven `429 Too Many Requests` con `Retry-After` (cabeceras nativas del middleware `throttle`).

> ⚠️ **Pendiente (deuda introducida por ADR-0003, 2026-06-24):** el registro de asistencia real
> corre por el componente Livewire `AttendanceRegistration` (endpoint `/livewire/update`), que **no**
> pasa por `throttle:attendance`. Hoy ese registro **no tiene rate limiting**. Decidir entre:
> (a) aplicar un limitador al endpoint Livewire / a una acción del componente, o
> (b) retirar `throttle:attendance` y su config `throttle.attendance` si se asume el riesgo.

### Notas
- **`auth` (login):** ya estaba cubierto por el starter kit — `App\Livewire\Auth\Login` limita a
  **5 intentos** por `email + IP` (`RateLimiter::tooManyAttempts`). No se modificó.
- **`api-stats` generoso a propósito:** el panel dispara ~17 peticiones en paralelo por carga; un
  límite bajo daría falsos positivos.
- **Endpoints `/api/statistics/event/{event}/*`:** quedan protegidos por autorización por-evento
  (`$authorizeStatisticsEvent`, 403). No llevan throttle aún; ampliar si se observa scraping.
- **Rutas de prueba sin auth** (`/api/events`, `/api/participants`, …) siguen abiertas — eso es
  [[brechas-conocidas]] #4 (decisión de **auth**, no de rate limit). Fuera del alcance de este ADR.

## Pendiente (operación / despliegue)
- [ ] **Trusted proxies en Hostinger:** verificar que Laravel resuelve la IP real tras el proxy
  (`X-Forwarded-For`) para que el límite por IP no agrupe a todos bajo una sola. Es config de
  `bootstrap/app.php` (`trustProxies`) y depende de la infra; **no** se tocó aquí para no introducir
  una mala configuración a ciegas.
- [ ] (Opcional) Defensa de borde (Cloudflare/WAF) para DDoS volumétrico — capa de red, complementaria.

## Relacionado
[[arquitectura]] · [[brechas-conocidas]] · [[registro-de-asistencia]]
