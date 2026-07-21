---
tipo: metodologia
descripcion: Pasos para actualizar el sistema en Hostinger dejando todo funcional (migraciones, backfills, cron)
actualizado: 2026-07-05
---

# Guía de actualización / despliegue (Hostinger)

Tras subir una versión nueva a producción hay datos que **no se poblan solos** (sobre todo los
**PDF de formato**). Esta guía deja el sistema **completamente funcional** con las últimas
actualizaciones (ADR-0004, 0015, 0017, 0018). Hosting compartido, sin workers permanentes.

## Paso 0 (local, antes de subir): compilar y versionar assets

`public/build` está en `.gitignore`, así que el build **hay que añadirlo a la fuerza** o
producción se queda con CSS/JS viejos (clases de Tailwind nuevas ni existen — pasó con el
banner de anuncios). Secuencia estándar acordada (2026-07-19):

```bash
npm run build
git add .
git add -f public/build
git commit -m "chore: update assets"
git push
```

## El servidor solo necesita el código de la app (sparse-checkout)

El repo trae carpetas que no pintan nada en producción (`vault/`, `.claude/`, `tests/`,
`CLAUDE.md`…). En el clon **del servidor**, una sola vez:

```bash
git sparse-checkout set --no-cone '/*' \
    '!/vault/' '!/.claude/' '!/tests/' \
    '!/CLAUDE.md' '!/AGENTS.md' '!/TEST.md'
git checkout
```

Desde entonces, cada `git pull` trae las actualizaciones **sin materializar** esas rutas
(y borra las que ya estuvieran). Para revertir: `git sparse-checkout disable`.
Candidatas adicionales según se quiera: `'!/docs/'`, `'!/tools/'`.

⚠️ Esto es comodidad, no seguridad: si el repo vive dentro del docroot público, verificar
igualmente que el dominio apunte a `public/` y que nada fuera de `public/` sea navegable.

## Pasos (en orden)

1. **Mantenimiento (opcional):** `php artisan down`.
2. **Traer el código** (git pull o subir archivos).
3. **Dependencias:** `composer install --no-dev --optimize-autoloader`
   (`phpoffice/phpspreadsheet` ya viene con `maatwebsite/excel`).
4. **Migraciones:** `php artisan migrate --force`. Añade, entre otras:
   `import_batches.error_message`, `notifications`, `events.reminder_notified_at`/`ending_notified_at`,
   `formats.mapping_outdated`, `format_files`.
5. **Backfill de PDF de formato → BD (ADR-0017):**
   ```bash
   php artisan formats:pdf-a-bd
   ```
   ⚠️ **Correr ANTES de borrar/perder `public/formats`**: copia los PDF *desde el disco* a la BD. Es
   idempotente (saltar lo ya copiado). Después de esto, los PDF sobreviven aunque se borre la carpeta.
6. **Cron único (ADR-0004 + 0018):** una sola línea en el panel de cron de Hostinger:
   ```
   * * * * * cd /home/USUARIO/dominio && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
   ```
   Ese tick procesa: la **cola** de importación asíncrona (`queue:work --stop-when-empty`), el
   **escaneo de avisos de eventos** y las **limpiezas de retención**.
7. **Cachés:** `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
   `config:cache` **ya es seguro**: ADR-0015 quitó la escritura a `config/` en tiempo de ejecución.
8. **Permisos:** `storage/` y `bootstrap/cache` escribibles; `public/formats` escribible (sigue
   siendo destino de subida y respaldo secundario del PDF).
9. **Fin de mantenimiento:** `php artisan up`.

## Sobre los mapeos de coordenadas

Los **mapeos ya viven en la BD** (`formats.mapping`) para los formatos gestionados desde la UI → **no
requieren backfill**. Los formatos **legado** que solo existan en `config/attendance_formats.php`
siguen funcionando por *fallback* de lectura. Nota honesta: mientras no se haga el backfill de esos
formatos legado a filas `formats` (pendiente en [[adr-0015-mapeo-de-formatos-fuente-de-verdad-en-bd]]
y [[adr-0017-pdf-de-formato-en-bd]]), **dependen del PDF en disco**; los formatos creados/editados por
la UI ya no.

## Verificación rápida

- **Formatos:** descargar el PDF de asistencia de un evento (debe salir; si corriste el backfill,
  incluso con `public/formats` borrado).
- **Importación:** subir un **CSV** pequeño (procesa inline, al instante) y un **.xlsx grande**
  (queda "procesando" y se completa tras el siguiente tick del cron).
- **Notificaciones:** la campana debe mostrar el aviso "lote listo para revisar".

## Reglas / no olvidar

- El **backfill de PDF** debe correr con los archivos **aún en disco**.
- **Sin el cron**, los `.xlsx` grandes quedan "procesando" para siempre (nadie procesa la cola).
- `QUEUE_CONNECTION=database` (ya en `.env`).
- Ventanas de aviso y retención son configurables en `config/notifications.php` (opcional).
- Si `formats:pdf-a-bd` avisa "Sin archivo en disco" para algún formato, ese formato no tenía su PDF
  y hay que volver a subirlo desde la UI.

## Relacionado
[[adr-0017-pdf-de-formato-en-bd]] · [[adr-0015-mapeo-de-formatos-fuente-de-verdad-en-bd]] ·
[[adr-0004-pasarela-de-revision-para-importacion-de-participantes]] ·
[[adr-0018-centro-de-notificaciones-in-app]] · [[reglas-de-oro]]
