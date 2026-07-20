---
tipo: adr
descripcion: ADR-0031 — Acumulado diario de impresiones/clics de banners para reportes por rango de fechas
actualizado: 2026-07-19
---

# ADR-0031 · Estadísticas diarias de banners

- **Estado:** 🟢 Implementado (2026-07-19)
- **Fecha:** 2026-07-19
- **Contexto del repo:** fase 3 de [[plan-banner-anuncios]]; complementa
  [[adr-0030-banner-de-anuncios-en-registro-publico]].

## Contexto

Los contadores `banners.impressions/clicks` son totales de vida completa: no pueden responder
"¿cuántas impresiones tuvo el patrocinador en marzo?", que es lo que se factura.

## Decisión

Tabla **`banner_daily_stats`** (banner_id, date, impressions, clicks, `unique(banner_id, date)`):
cada impresión/clic incrementa **ambos** niveles (total del banner + fila del día, con
`firstOrCreate` + increment del query builder). El reporte y su export a Excel
(`BannerReportExport`) leen solo del acumulado diario.

La impresión, además, ya no se cuenta al renderizar la página sino vía
`POST /banners/{id}/impresion` disparado con `sendBeacon` **solo cuando el banner se muestra**
(fase 2 del plan): las métricas facturables deben ser defendibles ante el patrocinador.

## Alternativas consideradas

- **Solo contadores totales + "foto" mensual manual** — frágil, sin rangos arbitrarios.
- **Registrar cada hit como fila (event log)** — máxima granularidad, pero crece sin límite y
  este volumen (cientos/día) no lo justifica; el día es la unidad de facturación real.

## Consecuencias

- ➕ Reportes por rango con CTR, exportables como evidencia.
- ➖ Dos escrituras por hit (total + día); irrelevante a este volumen.
- 🔁 Si algún día se necesita por hora u origen, se migra a event log y se recalcula el diario.
