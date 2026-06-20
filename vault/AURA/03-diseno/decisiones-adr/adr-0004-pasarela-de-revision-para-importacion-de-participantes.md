---
tipo: adr
descripcion: ADR-0004 (propuesta) — Pasarela de revisión (staging) para la importación masiva de participantes
actualizado: 2026-06-20
---

# ADR-0004 · Pasarela de revisión para importación de participantes

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-20
- **Contexto del repo:** `app/Http/Controllers/Configuration/ParticipantImportController.php`
  (`participants-import.import`), formulario en `resources/views/administration/participants/index.blade.php`
  (drop zone), `tests/Feature/Configuration/ProgramImportTest.php`, `QUEUE_CONNECTION=database`,
  `maatwebsite/excel`. Modelo de datos en [[modelo-de-datos]].

## Contexto
Hoy el Excel de participantes se importa **directo a las tablas principales**
(`participants`, `participant_roles`). Esto expone a errores del usuario difíciles de revertir:
listas con datos desactualizados, filas duplicadas o mal clasificadas que entran en bloque.
Además, una importación grande corre **dentro del request** (puede tardar/bloquear) y el botón
de subir permite **doble envío**.

## Decisión propuesta
Introducir una **pasarela de revisión** (staging) entre la carga y el commit:

1. **Tablas temporales** (migración nueva):
   - `import_batches` — un lote por carga: `user_id`, `filename`, `status`
     (`procesando` → `en_revision` → `aprobado` / `rechazado`), contadores
     (nuevos / actualizaciones / duplicados / inválidos), `created_at`.
   - `staged_participants` — una fila por registro del Excel: `import_batch_id`, datos crudos
     (JSON) + campos parseados, `row_status` (`nuevo` / `actualiza` / `duplicado` / `invalido`),
     `error` legible.
2. **Carga asíncrona (colas, no “hilos”):** el parseo y validación se hacen en un **job en cola**
   (`QUEUE_CONNECTION=database` ya disponible; `maatwebsite/excel` soporta importaciones
   `ShouldQueue`). PHP no tiene hilos en el request: el equivalente correcto es **encolar** y
   procesar con un worker (`php artisan queue:work`). El request solo crea el lote y devuelve.
3. **Revisión y aprobación:** una vista lista el lote (filtrable por `row_status`), permite
   descartar filas y **aprobar**. Solo al aprobar se hace el commit transaccional a las tablas
   principales (reutilizando la lógica de roles/duplicados que ya existe en el importador).
4. **Anti doble-cargue:** botón con `wire:loading.attr="disabled"` / estado Alpine + **token de
   idempotencia** por lote (un reenvío del mismo formulario no crea un segundo lote).
5. **Indicador de progreso:** el job actualiza `status`/contadores del lote; la UI muestra
   “procesando…” y refresca (Livewire `wire:poll` corto o evento) hasta `en_revision`.

## Consecuencias
- ➕ **Seguridad**: ninguna carga masiva entra sin revisión humana → no más “cargue masivo incorrecto”.
- ➕ Vista previa con clasificación (nuevo/actualiza/duplicado/inválido) antes de confirmar.
- ➕ El proceso pesado sale del request (no bloquea), sin doble envío.
- ➖ Más **esquema** (2 tablas), más UI, y requiere un **worker de cola corriendo** en producción
  (en Render: un proceso/worker adicional o `queue:work` gestionado).
- 🔁 Hay que migrar el flujo actual del importador y adaptar/ampliar `ProgramImportTest` y añadir
  pruebas del staging.

## Alternativas consideradas
- **Validar y commitear en el request con confirmación previa** (sin tabla): más simple, pero
  sigue bloqueando en cargas grandes y no deja rastro revisable del lote.
- **Procesar en background sin staging** (solo cola): resuelve el bloqueo pero **no** la revisión
  previa, que es el objetivo central.

## Pendiente para aceptar
- [ ] Confirmar disponibilidad de un **worker de cola** en el despliegue (Render).
- [ ] Definir política de retención/limpieza de lotes ya aprobados/rechazados.
- [ ] Rama sugerida: `feat/pasarela-importacion-participantes` (🔴 crea migraciones).

## Relacionado
[[mapa-de-modulos]] · [[modelo-de-datos]] · [[brechas-conocidas]] · [[nombres-de-rama-sugeridos]]
