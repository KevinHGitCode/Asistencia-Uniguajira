---
tipo: historia-usuario
descripcion: El admin carga participantes en masa, revisa antes de confirmar y recibe aviso cuando un archivo grande termina
actualizado: 2026-06-25
---

# HU-0006 · Cargar participantes en masa con revisión previa y aviso

**Como** administrativo,
**quiero** **cargar participantes desde Excel/CSV**, **revisar** qué entraría antes de confirmar y
que la app **me avise** cuando un archivo grande termine de procesarse,
**para** poblar la base sin errores y sin quedarme esperando frente a la pantalla.

## Contexto / valor
Cargar listas a mano es lento y propenso a errores. La app permite **carga masiva**, pero con una
**pasarela de revisión**: nada entra a las tablas reales hasta que un humano **aprueba** el lote,
viendo cuántos son **nuevos / actualizan / omitidos** y por qué se omite cada fila. Para archivos
grandes, el procesamiento corre **en segundo plano** y una **notificación** avisa cuando el lote
está listo para revisar — el administrativo puede seguir trabajando mientras tanto.

## Criterios de aceptación
- [x] Subo un archivo `.xlsx`/`.xls`/`.csv` desde una zona de arrastre.
- [x] El sistema **clasifica** cada fila en **nuevo / actualiza / omitido** y muestra el **motivo**
      de cada omisión; puedo **descargar los omitidos**.
- [x] **Nada se guarda** en las tablas reales hasta que **apruebo** el lote (confirmando con mi
      contraseña); puedo **rechazarlo** sin efectos.
- [x] Archivos **pequeños/CSV** se procesan al instante; los **`.xlsx` grandes** se procesan en
      **segundo plano** y la pantalla muestra "Procesando…" hasta quedar listos.
- [x] Recibo una **notificación in-app** (campana) cuando un lote procesado en segundo plano queda
      **listo para revisar**.

## Estado
🟢 **Implementada** — pasarela de revisión y commit transaccional en
`ParticipantImportController`; encolado híbrido en `ParseParticipantImportJob`; aviso vía
`ImportBatchReady` + campana `NotificationBell`. Decisiones en
[[adr-0004-pasarela-de-revision-para-importacion-de-participantes]] y
[[adr-0018-centro-de-notificaciones-in-app]].

## Notas técnicas
- En producción (Hostinger) el procesamiento en segundo plano lo dispara un **cron**
  (`schedule:run` → cola). Sin ese cron, los archivos grandes quedan "Procesando…".
- Para listas muy grandes, **CSV** es ~8× más rápido que `.xlsx`.

## Pruebas relacionadas
`tests/Feature/Configuration/ParticipantStagingImportTest.php`,
`tests/Feature/Configuration/ParticipantImportAsyncTest.php`,
`tests/Feature/NotificationCenterTest.php`.
