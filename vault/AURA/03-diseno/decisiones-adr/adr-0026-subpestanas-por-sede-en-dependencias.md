---
tipo: adr
descripcion: ADR-0026 (propuesta) — Sub-pestañas por sede en Dependencias (en vez de un desplegable) con contador por sede
actualizado: 2026-06-25
---

# ADR-0026 · Sub-pestañas por sede en Dependencias

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-25
- **Contexto del repo:** `app/Livewire/Administration/DependencyTable.php` +
  `resources/views/livewire/administration/dependency-table.blade.php` (hoy filtra sede con un
  `<select>` "Todas las sedes"). Sedes en `campuses` ([[modelo-de-datos]]).

## Contexto
En **Dependencias**, la sede se elige con un **desplegable**. Para comparar cuántas dependencias
tiene cada sede hay que abrir el menú y cambiar de opción una por una. El patrón de **pestañas** ya
gusta y se usa en el módulo de administración.

## Decisión
Reemplazar el desplegable de sede por **sub-pestañas**, una por **sede existente** (más una "Todas").
Al cambiar de pestaña:
- se filtra el listado por esa sede,
- el **contador** se actualiza al número de dependencias **de esa sede**.

Así se **compara de un vistazo** cuántas dependencias tiene cada sede, solo cambiando de pestaña.

## Consecuencias
- ➕ Comparación rápida entre sedes; navegación más directa que el desplegable.
- ➕ Coherente con el patrón de pestañas del módulo de administración.
- ➖ Con **muchas** sedes las pestañas pueden no caber → prever scroll horizontal/overflow.
- 🔁 Patrón replicable a otros listados con sede (programas, etc.) si funciona bien.

## Alternativas consideradas
- **Desplegable actual** — ocupa poco pero esconde la comparación entre sedes.
- **Pestañas + buscador** para muchas sedes — combinable si el número crece.

## Relacionado
[[adr-0025-componente-select-personalizado]] · [[migracion-multi-sede]] · [[mapa-de-modulos]]
