---
tipo: adr
descripcion: ADR-0030 — Banner discreto de anuncios/patrocinios en la página pública de registro por QR, con banners propios administrables
actualizado: 2026-07-19
---

# ADR-0030 · Banner de anuncios en el registro público (QR)

- **Estado:** 🟢 Implementado (2026-07-19) — banners propios; red de anuncios diferida
- **Fecha:** 2026-07-19
- **Contexto del repo:** página pública `events/access.blade.php` (`EventController::access`),
  administración en `resources/views/administration/banners/`, modelos `Banner`/`BannerFile`,
  `Configuration\BannerController`.

## Contexto

Se quiere obtener remuneración económica mostrando un anuncio en la página pública de registro
de asistencia (la que abre el participante al escanear el QR). Debe ser **no bloqueante**: un
banner pequeño y discreto en la parte inferior, sin degradar la experiencia de registro.

## Decisión

**Infraestructura de banners propios** (patrocinadores directos) en lugar de integrar una red de
anuncios de entrada:

1. **Modelo `Banner`** — nombre, enlace opcional, ventana de vigencia (`starts_at`/`ends_at`),
   `active`, contadores `impressions`/`clicks`. La **imagen vive en la BD** (`banner_files`,
   base64 en `longText`), mismo patrón que los PDF de formato ([[adr-0017-pdf-de-formato-en-bd]]).
2. **Selección**: en `EventController::access` se elige **un banner al azar** entre los vigentes
   (scope `vigentes()`) y se cuenta la impresión (query builder, sin tocar `updated_at`).
3. **Página pública**: parcial `partials/ad-banner.blade.php` — fijo abajo, ~50 px, etiqueta
   vertical «Publicidad», **descartable** (X, recordado por `sessionStorage`), imagen `lazy`,
   **sin scripts de terceros** (la página del QR sigue ligera, sin React).
4. **Clics**: ruta pública `banners.click` (`/banners/{id}/ir`) que incrementa el contador y
   redirige (`rel="nofollow sponsored"`); imagen servida por `banners.image` desde la BD con
   `Cache-Control` y `ETag`. Ambas con `throttle:public`.
5. **Administración**: CRUD **solo superadmin** en `/administracion/banners` (tabla con vista
   previa, vigencia, contadores; modales centrados [[adr-0006-formularios-en-modal-centrado]]),
   con registro en `ActivityLogService` (módulo `banners`).

## Consecuencias

- ➕ Monetización posible desde ya con patrocinadores directos; métricas (impresiones/clics) para
  rendir cuentas.
- ➕ Sin dependencia de aprobación externa ni cookies de terceros → sin banner de consentimiento
  adicional por ahora.
- ➖ Conseguir patrocinadores es trabajo comercial, no técnico.
- ⚠️ **Pendiente no técnico:** aclarar la **autorización institucional** para mostrar publicidad
  en una página del dominio de la universidad, antes de vender espacios reales.
- 🔁 ~~Si más adelante se aprueba una red (AdSense), se enchufa en el mismo hueco del parcial~~
  → **Revisado el 2026-07-20:** no se hará. La política de AdSense prohíbe anuncios en páginas
  sin contenido propio, y la del QR es un formulario. Los anuncios de red irán en una landing
  aparte; esta superficie se queda **solo con banners propios**
  ([[adr-0032-anuncios-de-red-solo-sobre-contenido-propio]]).

## Alternativas consideradas

- **Google AdSense de entrada** — descartado por ahora: aprobación incierta en una página de bajo
  contenido, exige consentimiento/cookies, e ingresos marginales con este tráfico.
- **Imagen en disco público** — descartada: en Hostinger el disco público no es durable (misma
  razón que [[adr-0017-pdf-de-formato-en-bd]]).
