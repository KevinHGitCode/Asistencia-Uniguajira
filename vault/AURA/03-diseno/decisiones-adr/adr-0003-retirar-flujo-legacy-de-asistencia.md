---
tipo: adr
descripcion: ADR-0003 (implementado) — Retirar el flujo legacy de asistencia del AttendanceController
actualizado: 2026-06-24
---

# ADR-0003 · Retirar el flujo legacy de asistencia

- **Estado:** 🟢 Implementado (2026-06-24)
- **Fecha:** 2026-06-20 (implementado 2026-06-24)
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

## Implementación (2026-06-24)
Se **eliminó** por completo el flujo legacy (se optó por borrar, no redirigir, al confirmar que
el único acceso público es `events.access` + el componente Livewire `AttendanceRegistration`):
- [x] Borrado `app/Http/Controllers/AttendanceController.php` (`store` + `confirmation`).
- [x] Borrada la vista `resources/views/events/confirmation.blade.php`.
- [x] Eliminadas las rutas `attendance.store` (POST) y `attendance.confirmation` (GET) y el
  `use ... AttendanceController` en `routes/web.php`.
- [x] Verificado que `events/access.blade.php` monta `<livewire:event.attendance-registration>` y
  que el componente tiene su propio paso `success` (no usa la vista ni la ruta legacy).
- [x] Pruebas: se retiraron de `RateLimitingTest` los dos casos que ejercían `attendance.store`;
  queda el caso de `events.access` (`throttle:public`), que pasa.

> ⚠️ **Consecuencia para [[adr-0005-rate-limiting-anti-abuso]]:** el limitador `throttle:attendance`
> solo estaba aplicado a la ruta legacy `attendance.store`. El registro real corre por Livewire
> (`/livewire/update`), que **no** pasa por ese limitador. Al retirar la ruta, `throttle:attendance`
> queda **huérfano** y el registro de asistencia queda **sin rate limiting**. Pendiente decidir si
> se aplica un limitador al endpoint de Livewire o se retira la config `throttle.attendance`.

## Relacionado
[[brechas-conocidas]] · [[registro-de-asistencia]] · [[nombres-de-rama-sugeridos]]
