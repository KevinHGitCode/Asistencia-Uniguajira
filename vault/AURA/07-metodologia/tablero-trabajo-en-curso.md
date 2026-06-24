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

---
> Cambios de esquema = migraciones nuevas/alteradas. Mira el orden cronológico en
> `database/migrations/` y [[modelo-de-datos]] antes de añadir una.
