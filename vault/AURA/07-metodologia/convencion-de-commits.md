---
tipo: metodologia
descripcion: Formato de mensajes de commit del proyecto
actualizado: 2026-06-20
---

# Convención de commits

Alineada con los prefijos de [[convencion-de-ramas]] (estilo Conventional Commits, en español).

## Formato
```
<tipo>: <resumen en imperativo y minúscula>

[cuerpo opcional: por qué, no el qué]
```

## Tipos
`feat` · `fix` · `refactor` · `docs` · `test` · `chore` (tareas varias: deps, config).

## Reglas
- Resumen **corto** (≤ ~72 car.), en **imperativo**: "agrega", "corrige", "quita".
- En español, coherente con el historial actual del repo (p.ej. `feat: se agrega paginacion…`).
- Un commit = un cambio coherente. Evita "varios arreglos" en uno.
- Referencia el issue/ADR si aplica: `refactor: retira flujo legacy de asistencia (ADR-0003)`.

## Ejemplos (del estilo del repo)
```
feat: se agrega paginacion a las tablas de programas y organizaciones
refactor: se quita la funcionalidad de eliminar log
docs: actualiza README con el stack real
test: agrega cobertura del registro de asistencia
```

> El cuerpo es para el **por qué**. El qué ya está en el diff.
