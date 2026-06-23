---
tipo: metodologia
descripcion: Tablero para reservar tareas antes de tocar código y evitar choques
actualizado: 2026-06-20
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
| _(ejemplo)_ Crear el vault AURA | equipo | `docs/crear-vault-aura` | 🟢 | 2026-06-20 |
| Rate limiting anti-abuso (ADR-0005) | equipo | `feat/rate-limiting-rutas` | 🔴 | 2026-06-21 |
| Paleta de comandos admin · MVP (ADR-0007) | equipo | `feat/paleta-comandos-admin` | 🟢 | 2026-06-21 |
| Formularios de usuario a modal centrado (ADR-0006) | equipo | `refactor/formularios-modal-centrado` | 🟢 | 2026-06-21 |
| Retirar rutas API de prueba sin auth (ADR-0014) | equipo | `fix/retirar-rutas-api-de-prueba` | 🔴 | 2026-06-21 |
| Contenedor de eventos con búsqueda (ADR-0012) | equipo | `feat/eventos-busqueda-filtros` | 🟢 | 2026-06-21 |

## 🟨 En revisión (PR abierto)
| Tarea | Responsable | Rama | PR |
|---|---|---|---|
| | | | |

## 🟩 Hecho recientemente
| Tarea | Rama | Mergeado |
|---|---|---|
| | | |

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
