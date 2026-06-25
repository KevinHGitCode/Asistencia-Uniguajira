---
tipo: adr
descripcion: ADR-0004 (propuesta) — Pasarela de revisión (staging) para la importación masiva de participantes
actualizado: 2026-06-25
---

# ADR-0004 · Pasarela de revisión para importación de participantes

- **Estado:** 🟢 Aceptada — núcleo implementado (síncrono); encolado/async pendiente
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

## Restriccion multi-sede
Los `participants` son **globales**: una persona puede asistir a eventos de cualquier sede y no debe
pedirse ni guardarse una sede propia del participante. Por eso, la plantilla de importacion masiva
no debe incluir columna `Sede`, los formularios/listados de participantes no deben pedir sede, y los
filtros de participantes no deben filtrar directamente por sede.

Cuando el importador necesite resolver catalogos que si pertenecen a una sede (`programs` o
`dependencies`), la sede se deriva del contexto autorizado del usuario o del sufijo escrito dentro
de `Programa o Dependencia` (por ejemplo, `Ingenieria de Sistemas - Riohacha`). Ese sufijo
desambigua el programa/dependencia; no convierte al participante en un registro local de sede.

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
  (en **Hostinger** / hosting compartido normalmente no hay demonios persistentes: usar cron +
  `queue:work --stop-when-empty` o el scheduler, no un worker permanente).
- 🔁 Hay que migrar el flujo actual del importador y adaptar/ampliar `ProgramImportTest` y añadir
  pruebas del staging.

## Alternativas consideradas
- **Validar y commitear en el request con confirmación previa** (sin tabla): más simple, pero
  sigue bloqueando en cargas grandes y no deja rastro revisable del lote.
- **Procesar en background sin staging** (solo cola): resuelve el bloqueo pero **no** la revisión
  previa, que es el objetivo central.

## Progreso (2026-06-20)

**Incremento 1 — anti doble-cargue + UX:**
- [x] Anti doble-cargue (botón deshabilitado + guard Alpine + validación de archivo).
- [x] Indicador de carga (spinner + “Procesando…” + aviso de no recargar).
- [x] La vista abre en la **lista** y muestra el **conteo de participantes** bajo el título.

**Incremento 2 — pasarela de revisión (núcleo del ADR):**
- [x] Tablas `import_batches` y `staged_participants` (migraciones `2026_06_21_000001/2`) + modelos
  `ImportBatch` / `StagedParticipant` (casts JSON, borrado en cascada).
- [x] `ParticipantImportController::import` ahora **parsea a staging** (no commitea); el plan se
  guarda en `staged_participants` clasificado en `nuevo` / `actualiza` / `omitido`.
- [x] **Vista de revisión** (`review.blade.php`) con contadores, filtros por estado, tabla y
  acciones **Aprobar e importar** / **Rechazar** (la aprobación corre en una transacción y
  reutiliza la lógica original en `commitPlan`, recalculando roles activos al confirmar).
- [x] Rutas `participants-import.{review,approve,reject,batch-skipped}` + aviso de **lotes
  pendientes** en el índice + descarga de omitidos por lote.

**Incremento 3 — acceso, confirmación segura y velocidad:**
- [x] **Historial de importaciones** (`participants-import.batches` + `batches.blade.php`): se
  puede volver a cualquier lote ya procesado a **revisarlo o descargar sus omitidos**.
- [x] **Confirmación con modal** para aprobar y rechazar; **aprobar exige la contraseña** del
  admin (`current_password`) como re-autenticación antes de tocar la BD.
- [x] **Velocidad (commit):** `commitPlan` corre en una transacción y `persistStaging` envuelve
  todos los inserts en **una sola transacción** (en SQLite evita un fsync por sentencia).
  También se quitó el fetch de roles activos del parseo (estaba muerto) y se `disableQueryLog`.
- [x] **Tests de feature** (`ParticipantStagingImportTest`): staging sin tocar la tabla,
  contraseña obligatoria al aprobar, rechazo sin efectos, descarga de omitidos.

**Incremento 4 — fast-path de CSV:**
- [x] Lectura nativa de CSV con `fgetcsv` (`readCsvRows`): BOM, normalización de codificación
  (Windows-1252 → UTF-8) y detección de separador (`,` / `;` / tab). Para `.xlsx/.xls` se
  mantiene PhpSpreadsheet.
- [x] **Medición (10.000 filas, SQLite local):**
  - **CSV con fast-path:** `parse+staging` ≈ **0,54 s** (antes, CSV vía PhpSpreadsheet: 10,4 s).
  - **XLSX (sigue por PhpSpreadsheet):** `parse+staging` ≈ **4,2 s** — el fast-path NO aplica a xlsx.
  - `approve/commit` ≈ **0,6 s** en ambos.
  - → CSV es ~**8×** más rápido que xlsx ahora. Para máxima velocidad: subir **CSV**.

> Stored procedure descartado: durante el parseo los datos aún no están en la BD y no sería
> portable (SQLite local / MySQL en Hostinger). El commit ya es rápido por la transacción.

**Pendiente (solo para archivos `.xlsx` grandes o no bloquear el request):**
- [ ] **Encolar el parseo** (`ShouldQueue`) → quita la espera del request; en Hostinger compartido,
  vía cron + `queue:work --stop-when-empty`. El progreso pasaría a poll del estado del lote.
- [ ] Lectura por *chunks* para `.xlsx` muy grandes (o recomendar exportar a CSV, que ya es ~19× más rápido).
- [ ] Política de retención/limpieza de lotes y prueba en navegador con un Excel real.

> **Aviso de "lote listo" → ADR-0018.** Cuando el parseo se encole, el usuario no debería tener que
> quedarse en la pantalla esperando. La notificación de que **el lote ya está en revisión** es el
> primer consumidor del centro de notificaciones in-app
> ([[adr-0018-centro-de-notificaciones-in-app]]): ambos comparten el **mismo cron de Hostinger**
> (`schedule:run` dispara `queue:work --stop-when-empty` + el escaneo) y la misma política de
> retención/housekeeping.

## Pendiente para aceptar
- [ ] Si se encola el parseo: definir el mecanismo en **Hostinger** (cron + `queue:work --stop-when-empty`,
  ya que el hosting compartido no mantiene workers permanentes).
- [ ] Definir política de retención/limpieza de lotes ya aprobados/rechazados.
- [ ] Rama sugerida: `feat/pasarela-importacion-participantes` (🔴 crea migraciones).

## Relacionado
[[mapa-de-modulos]] · [[modelo-de-datos]] · [[brechas-conocidas]] · [[nombres-de-rama-sugeridos]]
