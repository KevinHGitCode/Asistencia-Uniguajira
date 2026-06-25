---
tipo: adr
descripcion: ADR-0017 (propuesta) — Guardar los PDF de formato en la base de datos en vez del filesystem
actualizado: 2026-06-24
---

# ADR-0017 · Guardar los PDF de formato en la base de datos

- **Estado:** 🟡 Propuesta
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

## Pendiente para aceptar
- [ ] Decidir esquema: columna BLOB en `formats` vs tabla `format_files` (+ `mime`, `size`, `hash`).
- [ ] Estrategia de archivo temporal para FPDI (carpeta `storage/app/tmp`, limpieza).
- [ ] Migración de los PDF actuales del disco a la BD.
- [ ] Rama sugerida: `feat/formatos-pdf-en-bd` (🔴 migración + cambia almacenamiento y generación).

## Relacionado
[[adr-0015-mapeo-de-formatos-fuente-de-verdad-en-bd]] · [[adr-0016-edicion-formato-muchas-dependencias]] ·
[[mapa-de-modulos]] · [[modelo-de-datos]]
