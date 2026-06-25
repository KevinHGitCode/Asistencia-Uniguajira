---
tipo: metodologia
descripcion: Convención de nombres de rama (prefijos + kebab-case) y base de trabajo
actualizado: 2026-06-20
---

# Convención de ramas

## Base
- Se trabaja siempre sobre **`develop`** (rama de integración). `master` es producción.
- Una rama por tarea; corta y enfocada.

## Formato
```
<prefijo>/<descripcion-en-kebab-case>
```
- **kebab-case**: minúsculas, palabras separadas por `-`, **sin acentos ni ñ** (usa `n`).
- Descripción corta y concreta (3–6 palabras).

## Prefijos
| Prefijo | Para qué | Ejemplo |
|---|---|---|
| `feat/` | Nueva funcionalidad | `feat/exportar-estadisticas-pdf` |
| `fix/` | Corrección de bug | `fix/duplicado-asistencia-concurrente` |
| `refactor/` | Reestructurar sin cambiar comportamiento | `refactor/retirar-asistencia-legacy` |
| `docs/` | Documentación / vault | `docs/actualizar-readme-stack` |
| `test/` | Solo pruebas | `test/cobertura-registro-asistencia` |

## 🟢 paralela / 🔴 serializa
Al proponer la rama, márcala:
- **🟢 paralela** — no toca migraciones ni rutas/contratos compartidos: puede ir en paralelo.
- **🔴 serializa** — toca **esquema** (migraciones), rutas públicas o contratos compartidos:
  coordina en [[tablero-trabajo-en-curso]] y evita solaparla con otra 🔴.

> El catálogo de ramas sugeridas por funcionalidad está en [[nombres-de-rama-sugeridos]].
> Mensajes de commit: [[convencion-de-commits]]. Reglas generales: [[reglas-de-oro]].
