---
tipo: plantilla
descripcion: Plantilla para notas de módulo (copiar a 02-producto/modulos)
actualizado: 2026-06-20
---

# Plantilla · Nota de módulo

> Copia a `02-producto/modulos/<slug-ascii>.md`. Crea una solo si aporta algo que el código no
> dice solo (decisiones, reglas de negocio, gotchas). Ejemplo real: [[registro-de-asistencia]].

```markdown
---
tipo: nota-modulo
descripcion: <una línea>
actualizado: AAAA-MM-DD
---

# Módulo: <nombre>

## Qué hace
<en 1–2 frases>

## Dónde vive (código)
- Controlador/Componente: `...`
- Vistas: `...`
- Rutas: `...`
- Servicios: `...`

## Flujo
<pasos o máquina de estados>

## Reglas de negocio clave
- <regla>

## Datos que toca
<tablas; enlaza a [[modelo-de-datos]]>

## Gotchas / deuda
- <trampa conocida; enlaza a [[brechas-conocidas]] o un ADR>

## Relacionado
<HU, CU, ADR>
```
