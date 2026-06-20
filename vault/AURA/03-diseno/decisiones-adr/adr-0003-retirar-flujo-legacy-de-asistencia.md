---
tipo: adr
descripcion: ADR-0003 (propuesta) — Retirar el flujo legacy de asistencia del AttendanceController
actualizado: 2026-06-20
---

# ADR-0003 · Retirar el flujo legacy de asistencia

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-20
- **Contexto del repo:** `AttendanceController` (`store`, `confirmation`), vista
  `resources/views/events/confirmation.blade.php`, rutas `attendance.store` y
  `attendance.confirmation` en `routes/web.php`.

## Contexto
El registro real de asistencia lo hace el componente Livewire `AttendanceRegistration`,
montado por `events/access.blade.php` (ver [[registro-de-asistencia]]). En paralelo siguen
**vivos** un controlador y rutas legacy que implementan una versión **más pobre** (solo busca
participantes existentes por documento, sin auto-registro ni detalle demográfico). Es una de
las [[brechas-conocidas]] (#1).

## Decisión propuesta
Eliminar el flujo legacy: la ruta POST `attendance.store`, el método `confirmation`, la vista
`events/confirmation` y el código muerto asociado; o, si se quiere conservar la ruta de
confirmación por compatibilidad, redirigirla al flujo Livewire.

## Consecuencias
- ➕ Menos superficie de ataque (una ruta pública POST menos) y menos confusión.
- ➕ Una sola fuente de verdad para el registro.
- ➖ Hay que verificar que **ningún** enlace/QR antiguo apunte a `attendance.store`.
- 🔁 Requiere revisar tests y referencias antes de borrar.

## Pendiente para aceptar
- [ ] Confirmar que no hay consumidores externos de `attendance.store`.
- [ ] Definir si se borra o se redirige `attendance.confirmation`.
- [ ] Rama sugerida: `refactor/retirar-asistencia-legacy` (🔴 toca rutas públicas, serializar).

## Relacionado
[[brechas-conocidas]] · [[registro-de-asistencia]] · [[nombres-de-rama-sugeridos]]
