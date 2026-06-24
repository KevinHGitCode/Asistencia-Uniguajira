---
tipo: estado-actual
descripcion: Brechas e inconsistencias entre lo que prometen UI/docs y lo que entrega el codigo
actualizado: 2026-06-20
---

# Brechas conocidas

Diferencias reales entre lo documentado/prometido y lo que el backend hace hoy. Cada brecha
es candidata a ticket, idea ([[ideas]]) o decision ([[plantilla-adr]]).

## 1. Doble flujo de registro de asistencia ✅ RESUELTO (2026-06-24)
- El flujo real es el componente Livewire `AttendanceRegistration`, montado por
  `events/access.blade.php` (`<livewire:event.attendance-registration>`).
- El flujo legacy (`AttendanceController::store`/`confirmation`, vista `events/confirmation`,
  rutas `attendance.store`/`attendance.confirmation`) fue **eliminado**
  → [[adr-0003-retirar-flujo-legacy-de-asistencia]].
- ⚠️ Brecha derivada nueva: el limitador `throttle:attendance` solo cubría la ruta legacy; el
  registro real (Livewire) **quedó sin rate limiting**. Ver ADR-0003 y
  [[adr-0005-rate-limiting-anti-abuso]].

## 2. README desactualizado 📄
- Dice "MySQL" y "Blade, Tailwind" como unico stack; omite Livewire, React, Alpine.
- Lista "Notificaciones automaticas"; en realidad solo hay correo de confirmacion de asistencia.
- No menciona administracion, formatos, organizaciones, auditoria ni estadisticas.

## 3. `CLAUDE.md` vs. codigo 🧭
- Declara `User belongsTo Dependency`; el codigo real es `belongsToMany` via `dependency_user`.
- Omite `ParticipantRole`, `AttendanceDetail`, `Organization`, `Format`, `ActivityLog`,
  `Campus` y `AcademicProgram`.
- Modelo real en [[modelo-de-datos]].

## 4. Estadisticas sin autenticacion / sin sede completa 🔓
- Los endpoints individuales `/api/statistics/*` en `routes/api.php` no estan migrados al
  filtro por sede.
- Riesgo: pueden leer agregados globales o mezclar sedes.
- Pendiente para la fase de estadisticas en [[migracion-multi-sede]].

## 5. Siembra de datos parcial 🌱
- `DatabaseSeeder` historicamente no deja un entorno demo completo.
- Revisar seeders actuales antes de asumir datos de prueba.

## 6. "AURA" es marca de la app y nombre del vault ⚠️
- `aura_blanco.png` y el footer "AURA" aparecen en la pagina publica de acceso.
- Este vault tambien se llama AURA. Mantener la distincion "app vs. conocimiento".

## 7. Tests de ejemplo sin limpiar 🧪
- Persisten tests placeholder del starter kit.
- Cobertura real en [[estrategia-de-pruebas]].

## 8. Migracion multi-sede incompleta por modulo 🟡
- Ya existe base estructural (`campuses`, `campus_id`, roles, `CampusScopeService`) y dashboard
  + calendario ya filtran por sede.
- Aun faltan modulos criticos: Eventos CRUD, Administracion, Estadisticas, Imports/Exports y
  auditoria.
- Riesgo: una query sin `CampusScopeService` puede mezclar Maicao/Riohacha/Fonseca/Villanueva.
- Fuente viva de verdad: [[migracion-multi-sede]].
