---
tipo: calidad
descripcion: Referencia de rendimiento del cargue masivo de participantes (baseline para futuras mediciones)
actualizado: 2026-06-20
---

# Benchmark — importación de participantes

Referencia para comparar mejoras futuras. Detalle de la decisión en
[[adr-0004-pasarela-de-revision-para-importacion-de-participantes]].

## Resultados (10.000 filas, SQLite local)

| Fase | CSV (fast-path `fgetcsv`) | XLSX (PhpSpreadsheet) |
|---|---|---|
| `parse + staging` | **0,54 s** | **4,2 s** |
| `approve / commit` | **0,6 s** | **0,6 s** |

- CSV es ~**8×** más rápido que xlsx tras el fast-path; antes, leer CSV por PhpSpreadsheet
  tardaba **10,4 s** (el fast-path lo bajó ~19×).
- El **commit** ya es rápido por la transacción única; el cuello de botella restante es el
  **parseo de xlsx** (PhpSpreadsheet).
- Extrapolado: un CSV de 30k ≈ **1,6 s**; un xlsx de 30k ≈ **13 s**.

## Cómo medir (reproducible)

- Cada lote guarda su tiempo de procesamiento en `import_batches.duration_ms`
  (parse + staging, en ms; solo BD, no se muestra en UI). Consultarlo da el dato real en
  producción sin instrumentar nada extra.
- En tests: generar un CSV/xlsx con N filas y cronometrar el POST a `participants-import.import`
  (ver `tests/Feature/Configuration/ParticipantStagingImportTest`).

## Recomendaciones

- Para máxima velocidad hoy: **subir CSV** (UTF-8).
- Para acelerar xlsx: encolar el parseo o lectura por chunks (pendiente en el ADR-0004).
