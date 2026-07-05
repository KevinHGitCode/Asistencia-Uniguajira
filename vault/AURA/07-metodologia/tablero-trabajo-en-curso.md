---
tipo: metodologia
descripcion: Tablero para reservar tareas antes de tocar código y evitar choques
actualizado: 2026-06-24
---

# Tablero de trabajo en curso

Reserva tu tarea **aquí** antes de escribir código (regla 1 de [[reglas-de-oro]]). Esto evita
que dos personas/IAs choquen, sobre todo en cambios 🔴 que tocan el esquema.

## Cómo se usa
1. Añade una fila en **En curso** con: tarea, responsable, rama propuesta
   ([[nombres-de-rama-sugeridos]]), 🟢/🔴 y fecha.
2. Si es 🔴 (toca migraciones/rutas públicas), revisa que no haya otra 🔴 solapada.
3. Al abrir PR, muévela a **En revisión**. Al mergear, a **Hecho** (o bórrala).

## 🟦 En curso
| Tarea | Responsable | Rama | Tipo | Desde |
|---|---|---|---|---|
| Importación asíncrona (ADR-0004) + Centro de notificaciones (ADR-0018) — **implementado y con tests verdes en la rama; pendiente de revisión/merge** | equipo | `feat/importacion-participantes-async` | 🔴 | 2026-06-25 |
| Mejoras módulo usuarios · diseño/UX (ADR-0010) | equipo | `feat/modulo-usuarios-mejoras` | 🟢 | 2026-06-24 |
| Migración multi-sede progresiva (ADR-0009) | equipo | `feat/multi-sede-*` | 🔴 | 2026-06-21 |

## 🟨 En revisión (PR abierto)
| Tarea | Responsable | Rama | PR |
|---|---|---|---|
| | | | |

## 🟩 Hecho recientemente
| Tarea | Rama | Mergeado |
|---|---|---|
| Crear el vault AURA | `docs/crear-vault-aura` | 2026-06-20 |
| Rate limiting anti-abuso (ADR-0005) | `feat/rate-limiting-rutas` | 2026-06-21 |
| Paleta de comandos admin · MVP (ADR-0007) | `feat/paleta-comandos-admin` | 2026-06-22 |
| Formularios de usuario a modal centrado (ADR-0006) | `refactor/formularios-modal-centrado` | 2026-06-22 |
| Retirar rutas API de prueba sin auth (ADR-0014) | `fix/retirar-rutas-api-de-prueba` | 2026-06-22 |
| Contenedor de eventos con búsqueda · MVP (ADR-0012) | `feat/eventos-busqueda-filtros` | 2026-06-23 |
| Breadcrumbs detalle de evento (ADR-0013) | `feat/breadcrumbs-evento` | 2026-06-23 |
| Componente select buscable + uso en formatos | `feat/componente-select` | 2026-06-24 |
| Retirar flujo legacy de asistencia (ADR-0003) | `refactor/retirar-asistencia-legacy` | 2026-06-24 |
| Filtros estructurados en participantes (ADR-0011) | `feat/participantes-filtros` | 2026-06-24 |
| Listado de participantes en React + API (ADR-0008) | `feat/participantes-listado-react` | 2026-06-24 |
| Usuarios: activos en vivo + estadísticas de uso (ADR-0010) | `feat/modulo-usuarios-mejoras` | 2026-06-24 |
| Filtros estructurados en eventos (ADR-0012) | `feat/eventos-busqueda-filtros` | 2026-06-24 |

## ⚠️ Avisos de serialización (🔴 activos)
> Lista aquí los cambios de **esquema/rutas públicas** en vuelo para que nadie los solape.

- Migracion multi-sede progresiva activa: antes de tocar eventos, administracion,
  estadisticas, imports/exports o usuarios, revisar [[migracion-multi-sede]] y confirmar
  si el modulo ya aplica `CampusScopeService`.
- Rate limiting (`feat/rate-limiting-rutas`, ADR-0005): las rutas publicas `events.access`,
  `attendance.store`, `attendance.confirmation`, la descarga de PDF y los grupos
  `/api/statistics/*` ahora pueden devolver **429**. Coordinar si tocas esos contratos.
- Retiro de rutas API de prueba (`fix/retirar-rutas-api-de-prueba`, ADR-0014): se **eliminaron** 12
  endpoints publicos sin auth de `routes/api.php` (`/api/events`, `/api/participants`, `/api/users`,
  `/api/programs`, `/api/dependencies`, etc.). Si los necesitabas, recrealos bajo `['web','auth']`.
- Importación asíncrona + notificaciones (`feat/importacion-participantes-async`, ADR-0004/0018,
  2026-06-25): **nuevas migraciones** `notifications`, `import_batches.error_message`,
  `events.reminder_notified_at`/`ending_notified_at`; nuevos estados de lote `procesando`/`error`;
  nueva ruta `participants-import.status`. Si tocas eventos, imports o el layout `app/sidebar`,
  coordina. Producción requiere el cron `schedule:run` activo.
- Mapeo de formatos en BD (misma rama, ADR-0015, 2026-06-25): migración
  `formats.mapping_outdated`; `FormatController` **ya no escribe** `config/attendance_formats.php`
  en runtime (se quitaron `updateConfigFile`/`removeFromConfigFile`/`arrayToPhp`). El `config/` queda
  como respaldo de solo lectura. Si tocas formatos o generación de PDF, coordina.
- Retiro de asistencia legacy (ADR-0003, 2026-06-24): se **eliminaron** las rutas publicas
  `attendance.store` (POST) y `attendance.confirmation` (GET), el `AttendanceController` y la vista
  `events/confirmation`. El registro publico unico es `events.access` + Livewire. Efecto colateral:
  `throttle:attendance` quedo huerfano y el registro real no tiene rate limiting (ver ADR-0005).

---
> Cambios de esquema = migraciones nuevas/alteradas. Mira el orden cronológico en
> `database/migrations/` y [[modelo-de-datos]] antes de añadir una.
