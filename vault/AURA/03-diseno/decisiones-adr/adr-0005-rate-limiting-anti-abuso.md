---
tipo: adr
descripcion: ADR-0005 (propuesta) — Rate limiting / throttling de rutas para mitigar abuso y DDoS
actualizado: 2026-06-20
---

# ADR-0005 · Rate limiting anti-abuso en rutas

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-20
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

## Pendiente para aceptar
- [ ] Acordar límites concretos por limitador.
- [ ] Verificar resolución de IP real tras el proxy de Hostinger (trusted proxies).
- [ ] Rama sugerida: `feat/rate-limiting-rutas` (🔴 cambia contratos de rutas).

## Relacionado
[[arquitectura]] · [[brechas-conocidas]] · [[registro-de-asistencia]]
