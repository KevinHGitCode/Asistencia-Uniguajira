---
tipo: estado-actual
descripcion: Brechas e inconsistencias entre lo que prometen UI/docs y lo que entrega el código
actualizado: 2026-06-20
---

# Brechas conocidas

Diferencias reales entre lo documentado/prometido y lo que el backend hace hoy. Cada brecha
es candidata a ticket, idea ([[ideas]]) o decisión ([[plantilla-adr]]).

## 1. Doble flujo de registro de asistencia (código legacy vivo) 🧟
- El flujo **real** es el componente Livewire `AttendanceRegistration`, montado por
  `events/access.blade.php` (`<livewire:event.attendance-registration>`).
- Siguen existiendo **vivos pero sin uso**: `AttendanceController::store` + `confirmation`,
  la vista `events/confirmation`, y las rutas `attendance.store` / `attendance.confirmation`.
- El flujo legacy es más pobre: solo busca participantes existentes por documento, sin
  auto-registro ni captura demográfica.
- **Riesgo:** ruta pública POST activa que duplica lógica y puede confundir. → Propuesta en
  [[adr-0003-retirar-flujo-legacy-de-asistencia]].

## 2. README desactualizado 📄
- Dice **"MySQL"** y **"Blade, Tailwind"** como único stack; omite Livewire, React, Alpine.
- Lista "Notificaciones automáticas" — en realidad solo hay **un correo** de confirmación de
  asistencia (`AttendanceRegisteredMail`), no notificaciones generales.
- No menciona administración, formatos, organizaciones, auditoría ni estadísticas.
- Stack real en [[stack-tecnologico]].

## 3. `CLAUDE.md` vs. código 🧭
- Declara `User belongsTo Dependency`; el código es **`belongsToMany`** vía `dependency_user`.
- Su tabla de relaciones **omite** `ParticipantRole`, `AttendanceDetail`, `Organization`,
  `Format`, `ActivityLog`. Modelo real en [[modelo-de-datos]].

## 4. Estadísticas sin autenticación 🔓
- Los endpoints **individuales** `/api/statistics/*` (en `routes/api.php`) **no** tienen
  middleware de auth. Solo `*-summary` usan `web, auth` para filtrar por rol.
- Permite leer agregados sin sesión. Riesgo pre-existente conocido — evaluar si es decisión
  consciente o deuda (candidato a ADR).

## 5. Siembra de datos parcial 🌱
- `DatabaseSeeder` solo ejecuta `ParticipantTypeSeeder` + `FormatSeeder` y crea **5 admins**
  con contraseña `12345678`. Dependencias, áreas, eventos, participantes y asistencias están
  **comentados**.
- Consecuencia: `php artisan migrate --seed` **no** deja un entorno de demo completo.

## 6. "AURA" es marca de la app y nombre del vault ⚠️
- `aura_blanco.png` y el footer "AURA" aparecen en la página pública de acceso → *AURA* es
  marca de la aplicación. Este vault también se llama AURA (decisión tomada). Mantener la
  distinción "app vs. conocimiento" en la comunicación.

## 7. Tests de ejemplo sin limpiar 🧪
- Persisten `tests/Unit/ExampleTest.php` y `tests/Feature/ExampleTest.php` (placeholders del
  starter kit). Cobertura real en [[estrategia-de-pruebas]].
