---
tipo: plantilla
descripcion: Plantilla para casos de uso (copiar a 02-producto/casos-de-uso)
actualizado: 2026-06-20
---

# Plantilla · Caso de uso

> Copia a `02-producto/casos-de-uso/cu-XXXX-slug-ascii.md`.
> Ejemplo real: [[cu-0001-descargar-pdf-asistencia]].

```markdown
---
tipo: caso-uso
descripcion: <una línea>
actualizado: AAAA-MM-DD
---

# CU-XXXX · <título>

## Actor
<quién, de [[personas-y-roles]]>

## Precondiciones
- <estado necesario antes de empezar>

## Flujo principal
1. <paso>
2. <paso>

## Flujos alternativos / errores
- <condición> → <resultado (p.ej. 403, validación)>

## Postcondiciones
- <qué queda cambiado / qué se entrega>

## Relacionado
<enlaces a [[modelo-de-datos]], módulo, ADR>
```
