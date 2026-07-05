---
tipo: adr
descripcion: ADR-0015 (propuesta) — El mapeo de formatos como única fuente de verdad en BD y sincronización al cambiar el PDF
actualizado: 2026-06-24
---

# ADR-0015 · Mapeo de formatos: única fuente de verdad en BD + sincronía al cambiar el PDF

- **Estado:** 🟢 Aceptada — núcleo implementado (2026-06-25, rama `feat/importacion-participantes-async`)
- **Fecha:** 2026-06-24
- **Contexto del repo:** `app/Http/Controllers/Configuration/FormatController.php`
  (`store`, `update`, `mapper`, `saveMapping`, `updateConfigFile`, `removeFromConfigFile`,
  `arrayToPhp`), `config/attendance_formats.php`, `app/Models/Format.php` (`mapping` cast `array`,
  `file`), `app/Services/AttendancePdfService.php` (`getConfig`).

## Contexto — cómo funciona hoy
Un **formato** es una plantilla PDF (`formats.file`) con un **mapeo** de coordenadas
(`formats.mapping`: `startY`, `rowHeight`, `maxRows`, `columns`, `header`, `file`, …) que indica
dónde escribir la tabla de asistencia sobre el PDF.

El mapeo se guarda **por duplicado**:
1. En la **BD** (`formats.mapping`, columna JSON / cast `array`).
2. En un **espejo** en `config/attendance_formats.php`: `saveMapping()` hace
   `$format->update(['mapping' => …])` **y** `updateConfigFile()`, que **regenera el archivo PHP**
   (`arrayToPhp` + `flock`) y ejecuta `Artisan::call('config:clear')` en cada guardado. `destroy()`
   hace lo simétrico con `removeFromConfigFile()`.

Al generar el PDF, `AttendancePdfService::getConfig()`:
- Si el `Format` tiene `mapping` en BD → lo usa y **sobreescribe** `['file']` con `$format->file`
  (el archivo actual).
- Si no → cae a `config("attendance_formats.{slug}")` y, en última instancia, a
  `config('attendance_formats.default')`.

Es decir: para formatos con mapeo en BD, **la BD ya manda**; el `config` es sobre todo un **espejo
redundante** (salvo el `default`, que solo vive en `config`).

## Problemas
1. **El espejo en `config/` es frágil y redundante.** Escribir un archivo PHP en `config/` en
   tiempo de ejecución + `config:clear` en cada guardado es delicado en **Hostinger (hosting
   compartido)**: permisos de escritura en `config/`, caché de configuración en producción
   (`config:cache`), y riesgo de corrupción/concurrencia. Duplica un dato que ya está en la BD.
2. **Cambiar el PDF deja el mapeo obsoleto (bug funcional).** `update()` con `pdf_file` actualiza
   `formats.file` y borra el archivo viejo, **pero no toca el mapeo**. El servicio sobreescribe el
   *nombre* del archivo, así que apunta al PDF nuevo, pero **las coordenadas siguen siendo las del
   PDF anterior** → la tabla se imprime descuadrada. El usuario debe acordarse de entrar a "Mapeo"
   y volver a guardar; **nada se lo advierte** (esto es justo lo que el usuario reportó).

## Decisión propuesta
- **BD como única fuente de verdad del mapeo.** `formats.mapping` (ya existe) es la autoridad.
  `AttendancePdfService` lee solo de la BD.
- **Retirar el espejo en `config/attendance_formats.php`** y la maquinaria asociada
  (`updateConfigFile`, `removeFromConfigFile`, `arrayToPhp`, `isSmallArray`, `arrayToInlinePhp`,
  el `.lock` y los `config:clear`). El **`default`** se conserva como constante/seed (no como archivo
  editable en runtime).
- **Sincronizar al cambiar el PDF:** en `store`/`update`, cuando se sube un `pdf_file`, marcar el
  mapeo como **desactualizado** y avisar al usuario (badge "Mapeo pendiente" en el listado + aviso
  al guardar) e invitar a re-mapear. Se conserva `formats.file` como fuente del archivo; el campo
  `mapping.file` se vuelve redundante.

## Consecuencias
- ➕ Una sola fuente de verdad; se elimina escritura a `config/` en runtime (mucho más robusto en
  hosting compartido y compatible con `config:cache`).
- ➕ El cambio de PDF deja de romper silenciosamente la generación: el usuario sabe que debe re-mapear.
- ➖ Hay que **migrar (backfill)** los mapeos que hoy solo estén en `config/` hacia `formats.mapping`.
- ➖ Reescribir parte de `AttendancePdfService::getConfig()` y `FormatController`.
- 🔁 Definir dónde vive el `default` (constante/seed) y un estado "mapeo desactualizado" (p. ej.
  columna `mapping_updated_at` vs `file_updated_at`, o flag `needs_mapping`).

## Alternativas consideradas
- **Mantener `config/` y solo auto-regenerarlo al subir el PDF** (bullet original "actualizar el
  config al cargar"): más barato, pero **perpetúa la duplicación** y la fragilidad de escribir en
  `config/` en producción. No resuelve la causa raíz.
- **No hacer nada:** persiste el bug de PDF descuadrado tras cambiar el archivo.

## Implementación (2026-06-25) — núcleo

Se implementó la parte de **mayor valor y menor riesgo**, sin tocar el camino de
generación del PDF (`AttendancePdfService::getConfig` ya prefería la BD):

- [x] **Se retiró la escritura en runtime a `config/`**: eliminados
  `updateConfigFile`, `removeFromConfigFile`, `arrayToPhp`, `isSmallArray`,
  `arrayToInlinePhp`, el `.lock` y los `Artisan::call('config:clear')` de
  `FormatController` (`saveMapping`/`destroy`). Ya **no** se muta `config/` en
  producción → robusto en Hostinger y compatible con `config:cache`.
- [x] **BD = fuente de verdad** de lo que se edita: `saveMapping` solo hace
  `$format->update(['mapping' => …, 'mapping_outdated' => false])`.
  `config/attendance_formats.php` queda como **respaldo estático de solo lectura**
  (para el `default` y los formatos legado), no editable en runtime.
- [x] **Estado "mapeo desactualizado":** columna `formats.mapping_outdated`
  (migración `2026_06_25_000004`). `update()` la marca en `true` cuando se sube un
  `pdf_file` nuevo y ya había mapeo; `saveMapping()` la vuelve `false`.
- [x] **Aviso al usuario:** flash al guardar ("el mapeo quedó pendiente") + badge
  **"Mapeo pendiente"** en el listado (`administration/formats/index.blade.php`).
  Helper `Format::needsMapping()`.
- [x] **Tests** (`FormatMappingSourceOfTruthTest`): guardar mapeo no reescribe el
  config; cambiar el PDF marca pendiente (y no lo hace si no había mapeo);
  eliminar funciona sin el espejo.

## Pendiente (opcional, no bloqueante)
- [ ] **Backfill** de los formatos legado de `config/attendance_formats.php`
  (general, bienestar, proyeccion_social, capacitacion_externa) a filas en
  `formats` para poder retirar del todo el archivo de config (hoy sigue como
  respaldo de solo lectura). Conlleva migración de datos → hacer con cuidado.
- [ ] Mover el `default` a constante/seed y que `getConfig` no dependa de `config()`.

## Relacionado
[[adr-0016-edicion-formato-muchas-dependencias]] · [[adr-0017-pdf-de-formato-en-bd]] ·
[[mapa-de-modulos]] · [[modelo-de-datos]]
