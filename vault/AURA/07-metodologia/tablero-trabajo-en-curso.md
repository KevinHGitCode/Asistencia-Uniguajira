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

- _(ninguno por ahora)_

---
> Cambios de esquema = migraciones nuevas/alteradas. Mira el orden cronológico en
> `database/migrations/` y [[modelo-de-datos]] antes de añadir una.
