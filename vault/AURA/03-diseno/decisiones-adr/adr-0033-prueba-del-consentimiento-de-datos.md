---
tipo: adr
descripcion: ADR-0033 — Guardar la prueba del consentimiento de tratamiento de datos (Ley 1581) y servir la política desde dominio propio
actualizado: 2026-07-20
---

# ADR-0033 · Prueba del consentimiento de tratamiento de datos

- **Estado:** 🟢 Aceptada (2026-07-20) — pendiente de implementación
- **Fecha:** 2026-07-20
- **Contexto del repo:** registro público `App\Livewire\Event\AttendanceRegistration` y su vista
  `resources/views/livewire/event/attendance-registration.blade.php`.

## Contexto

La **Ley 1581 de 2012** (protección de datos personales, Colombia) obliga al responsable del
tratamiento a obtener autorización previa e informada **y a conservar prueba de ella**. El
responsable aquí es la **Universidad de La Guajira**; el sistema es el medio de recolección.

Estado verificado el 2026-07-20 en el registro por QR:

- ✅ Hay casilla **obligatoria** de aceptación (`acceptsDataTreatment`, regla `accepted`), con
  enlace a la política de tratamiento de datos de la Universidad.
- ❌ **La aceptación no se persiste**: se valida en el formulario y se descarta al terminar
  (`AttendanceRegistration::589` la reinicia). No queda registro de quién aceptó, cuándo, ni
  bajo qué versión de la política.
- ⚠️ La política vive en un enlace de **Google Drive** externo, que puede cambiar, moverse o
  perder permisos; el consentimiento quedaría apuntando a un documento inaccesible.

Datos recogidos en ese formulario: nombre, documento, correo, teléfono, programa/dependencia u
organización — datos personales identificables, algunos de menores si el evento los incluye.

## Decisión

1. **Persistir la prueba del consentimiento** junto al registro de asistencia: marca de
   aceptación, **fecha/hora** y **versión del documento** de política aceptado. Sin la prueba,
   la casilla es una formalidad que no protege a nadie.
2. **Servir la política desde el propio dominio** (ruta pública y versionada), en vez de un
   enlace de Drive. El enlace externo puede seguir citándose como origen institucional, pero la
   versión que el usuario aceptó debe ser recuperable desde el sistema.
3. Cualquier uso de los datos **distinto del control de asistencia** (publicidad segmentada,
   cesión a patrocinadores, etc.) exige **autorización nueva y específica**: el principio de
   finalidad no se estira. Los banners de [[adr-0030-banner-de-anuncios-en-registro-publico]]
   **no** usan datos del participante, y así debe seguir.

## Consecuencias

- ➕ Se puede demostrar la autorización ante un reclamo o ante la SIC.
- ➕ El consentimiento deja de depender de un enlace externo fuera de control del proyecto.
- ➕ Base para atender los derechos del titular (consultar, corregir, suprimir, revocar) con
  plazos de ley (10 días hábiles consulta / 15 días hábiles reclamo).
- ➖ Una columna más por asistencia y una migración aditiva.
- ⚠️ **Pendiente no técnico:** validar el texto y el flujo con la oficina jurídica / el oficial
  de protección de datos de la Universidad. Este ADR es criterio de ingeniería, no asesoría
  legal. Ver también el registro de bases de datos ante la SIC (RNBD), que corresponde a la
  institución.
- 🔁 Habilita el paso 0 del orden fijado en
  [[adr-0032-anuncios-de-red-solo-sobre-contenido-propio]]: sin esto no se avanza a publicidad
  de red, porque los anuncios de terceros añaden cookies que también hay que declarar.

## Alternativas consideradas

- **Dejarlo como está** — descartada: la casilla sin prueba no cumple el requisito de
  demostrabilidad, y el riesgo recae sobre la Universidad.
- **Guardar solo un booleano** — insuficiente: sin fecha ni versión del documento no se puede
  saber *qué* fue lo aceptado, que es justo lo que se discutiría en un reclamo.
