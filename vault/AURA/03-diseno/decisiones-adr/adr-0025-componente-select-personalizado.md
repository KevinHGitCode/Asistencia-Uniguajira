---
tipo: adr
descripcion: ADR-0025 (propuesta) — Reemplazar los <select> nativos por un componente de select personalizado y consistente
actualizado: 2026-06-25
---

# ADR-0025 · Componente de select personalizado

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-25
- **Contexto del repo:** `<select>` nativos en filtros como
  `resources/views/statistics/partials/campus-filter.blade.php` ("Todas las sedes"),
  `livewire/administration/dependency-table.blade.php`, `program-table.blade.php`,
  `dashboard.blade.php`, formularios de usuarios/eventos. Ya existe un componente buscable en
  `resources/views/components/ui/searchable-select.blade.php`.

## Contexto
Varios selectores (p. ej. el de sede "**Todas las sedes**") son `<select>` **nativos**. Su apariencia
(flecha, lista desplegable, fuente) la decide el **navegador / sistema operativo**, así que **no
combinan** con el diseño (Tailwind/Flux) ni con el tema claro/oscuro, y se ven inconsistentes entre
equipos.

## Decisión
Estandarizar un **componente de select personalizado** (estilado con Tailwind/Flux, accesible con
teclado, compatible con tema oscuro) y usarlo en lugar de los `<select>` nativos cuando el control es
parte de la UI del producto. Para listas grandes se usa la variante **buscable** existente; para
listas cortas, una variante simple. Hereda la **dirección adaptativa** de
[[adr-0021-direccion-adaptativa-del-desplegable-buscable]].

## Consecuencias
- ➕ Apariencia **consistente** en todos los equipos y en modo oscuro.
- ➕ Reutiliza/expande el componente buscable que ya existe.
- ➖ Migrar varios `<select>` y cuidar **accesibilidad** (foco, teclado, lectores de pantalla) que el
   nativo daba gratis.
- 🔁 Inventariar los `<select>` nativos y migrarlos por tandas.

## Alternativas consideradas
- **Solo CSS sobre `<select>` nativo** — limitado: no se puede estilar del todo la lista desplegable.
- **Librería externa** (Choices.js, Tom Select) — potente pero añade dependencia; preferimos
  consolidar el componente propio que ya existe.

## Relacionado
[[adr-0021-direccion-adaptativa-del-desplegable-buscable]] ·
[[adr-0026-subpestanas-por-sede-en-dependencias]] · [[convenciones]]
