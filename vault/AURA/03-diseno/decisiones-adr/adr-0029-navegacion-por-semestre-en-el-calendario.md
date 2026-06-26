---
tipo: adr
descripcion: ADR-0029 (propuesta) — Navegar al semestre anterior/siguiente en el calendario solo si ese periodo tiene eventos
actualizado: 2026-06-25
---

# ADR-0029 · Navegación por semestre en el calendario

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-25
- **Contexto del repo:** módulo de calendario (`resources/views/calendar/`, vista de calendario del
  dashboard; animación CSS del indicador "Hoy" — ver [[mapa-de-modulos]] y `CLAUDE.md`).

## Contexto
El año académico se organiza por **semestres**. Cerca de un cambio de semestre (p. ej. fin de junio)
es útil mirar el **semestre anterior** o adelantarse al **siguiente**, pero hoy el calendario no
ofrece un salto directo entre periodos académicos.

## Decisión
Agregar controles para **retroceder al semestre anterior** y **avanzar al siguiente**, con una
**condición**: el control solo se habilita si en ese semestre **hay eventos registrados**. Así no se
navega a periodos vacíos.

- Definir el semestre (p. ej. **I = ene–jun**, **II = jul–dic**) de forma configurable.
- Habilitar "anterior"/"siguiente" según `exists` de eventos en ese rango (respetando el filtro por
  sede vigente, [[migracion-multi-sede]]).

## Consecuencias
- ➕ Navegación contextual por periodo académico, sin caer en meses vacíos.
- ➕ Útil justo en los cambios de semestre.
- ➖ Definir y mantener los **límites de semestre** (configurable) y consultas de existencia por rango.
- 🔁 Decidir si la navegación salta al **primer mes con eventos** del semestre destino.

## Alternativas consideradas
- **Solo navegación mensual (actual)** — funciona pero es lenta para saltar de periodo.
- **Mostrar siempre los botones** (sin condición de eventos) — llevaría a vistas vacías.

## Relacionado
[[mapa-de-modulos]] · [[migracion-multi-sede]] · [[modelo-de-datos]]
