---
tipo: adr
descripcion: ADR-0017 (propuesta) — Guardar los PDF de formato en la base de datos en vez del filesystem
actualizado: 2026-06-24
---

# ADR-0017 · Guardar los PDF de formato en la base de datos

- **Estado:** 🟢 Aceptada — implementado (2026-06-25, rama `feat/importacion-participantes-async`)
- **Fecha:** 2026-06-24
- **Contexto del repo:** `app/Http/Controllers/Configuration/FormatController.php`
  (`Storage::disk('formats')->storeAs/…/delete`, `validatePdfCompatibility`), `config/filesystems.php`
  (disco `formats`), `app/Models/Format.php` (`file`), `app/Services/AttendancePdfService.php`
  (lee el PDF con FPDI `setSourceFile`).

## Contexto
Las plantillas PDF se guardan en el **filesystem** (disco `formats`); en la BD solo queda el
**nombre** del archivo (`formats.file`). FPDI lee el PDF desde una **ruta de archivo**
(`setSourceFile($path)`).

En **Hostinger (hosting compartido)** esto trae fricción:
- Los archivos subidos **viven fuera del repo**; un despliegue/redeploy o un cambio de directorio
  puede dejarlos huérfanos o perderlos si no se respaldan aparte de la BD.
- El respaldo deja de ser "solo la BD": hay que respaldar también el directorio de formatos.
- Inconsistencias posibles: registro en BD sin archivo en disco (o al revés).

## Decisión propuesta
Guardar el **binario del PDF en la base de datos** (columna `BLOB`/`LONGBLOB` en `formats`, o una
tabla `format_files` aparte para no engordar la fila principal). En generación, `AttendancePdfService`
**vuelca el blob a un archivo temporal** y se lo pasa a FPDI (`setSourceFile`), porque FPDI exige una
ruta de archivo.

## Consecuencias
- ➕ El PDF viaja **junto a la BD**: respaldo/restauración y despliegues más simples y consistentes.
- ➕ Se elimina la dependencia del disco `formats` y los desajustes archivo↔registro.
- ➖ Crece el tamaño de la BD; ojo con `max_allowed_packet` de MySQL (PDF ≤ 5 MB hoy, holgado).
- ➖ Hay que **volcar a un archivo temporal por request** para FPDI (y limpiarlo); pequeño costo de I/O.
- 🔁 **Migración** de los PDF existentes del disco a la BD + ajustar `store`/`update`/`destroy` y la
  validación de compatibilidad (que hoy valida sobre la ruta en disco).

## Alternativas consideradas
- **Seguir en filesystem:** simple y eficiente, pero arrastra la fragilidad de archivos sueltos en
  hosting compartido.
- **Object storage (S3/compatibles):** robusto y escalable, pero añade dependencia/coste externos;
  excede el contexto actual (Hostinger compartido).

## Implementación (2026-06-25)

- [x] **Esquema:** tabla aparte `format_files` (`format_id` único cascade, `content`, `mime`, `size`,
  `hash`) para **no engordar** la fila `formats` (que se consulta a menudo). Migración
  `2026_06_25_000005`. Modelo `FormatFile` + relación `Format::fileRecord()`.
- [x] **Portabilidad del binario:** el PDF se guarda como **base64 en un `longText`**, no como
  `BLOB`. Motivo: `->binary()` de Laravel crea un `BLOB` de **64 KB** en MySQL (truncaría un PDF de
  5 MB); `longText` es `LONGTEXT` en MySQL y `TEXT` en SQLite, ambos manejan megabytes. Helpers
  `Format::storePdf()` / `pdfContents()` / `hasPdfInDb()`.
- [x] **Sin archivo temporal para FPDI:** se usa `StreamReader::createByString($bytes)` en
  `AttendancePdfService::generatePdf` (FPDI lee los bytes directo de memoria; no hace falta volcar a
  `storage/app/tmp` como se temía).
- [x] **BD durable, disco como respaldo:** la generación lee de la BD y **cae al disco** solo si no
  hay copia en BD. `store`/`update` guardan el PDF en la BD además del disco. → Si se borra
  `public/formats`, los PDF **siguen** en la BD.
- [x] **Backfill:** comando `formats:pdf-a-bd` (`BackfillFormatPdfs`, idempotente) copia a la BD los
  PDF de formato que hoy solo están en disco. Ejecutar una vez tras desplegar.
- [x] **Tests** (`FormatPdfInDatabaseTest`): subir guarda bytes en BD; el PDF sobrevive sin archivo
  en disco; `generatePdf` genera desde los bytes de la BD sin archivo en disco; el backfill copia
  disco → BD.

## Pendiente (opcional)
- [ ] Dejar de escribir el PDF al disco en `store`/`update` (hoy se guarda en ambos; el disco queda
  como caché). Requiere ajustar `validatePdfCompatibility` para validar desde bytes.
- [ ] Considerar `max_allowed_packet` de MySQL en Hostinger si se suben PDF cercanos al límite de 5 MB
  (base64 ≈ +33%).

## Relacionado
[[adr-0015-mapeo-de-formatos-fuente-de-verdad-en-bd]] · [[adr-0016-edicion-formato-muchas-dependencias]] ·
[[mapa-de-modulos]] · [[modelo-de-datos]]
