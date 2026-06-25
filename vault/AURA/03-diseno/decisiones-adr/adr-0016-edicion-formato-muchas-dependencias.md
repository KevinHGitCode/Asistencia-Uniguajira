---
tipo: adr
descripcion: ADR-0016 (propuesta) — Editar un formato con muchas dependencias hace el formulario inutilizable
actualizado: 2026-06-24
---

# ADR-0016 · Edición de formato con muchas dependencias (UX)

- **Estado:** 🟢 Implementado (opciones 1 y 2; opción 3 diferida) — 2026-06-24
- **Fecha:** 2026-06-24
- **Contexto del repo:** `resources/views/components/formats/form-modal.blade.php`
  (`<x-ui.multi-searchable-select :options="$dependencies" x-model="selectedDependencies">`),
  `app/Http/Controllers/Configuration/FormatController.php` (`store`/`update` → `dependencies()->sync`),
  `resources/views/administration/formats/index.blade.php`, `resources/js/administration/formats/formats-manager.js`.

## Contexto
La asignación de dependencias a un formato se hace en el modal de formato con un
`x-ui.multi-searchable-select`, que pinta **cada dependencia seleccionada como un chip**. El modal
es `max-w-lg`.

Cuando un formato tiene **muchas** dependencias (p. ej. **100**), al editarlo el control muestra los
~100 chips dentro del modal: crece sin límite, empuja los botones fuera de vista y se vuelve
**inutilizable**. Además, asignar/quitar de a una entre cientos es tedioso y propenso a errores.

> Nota: las dependencias pueden crecer aún más con multi-sede (la misma dependencia por sede), así
> que el problema empeora con el tiempo.

## Decisión propuesta
Mejorar la UX de selección masiva (a afinar en implementación). Opciones, combinables:

1. **Acotar la visualización de seleccionadas:** área con **alto máximo + scroll** y un **contador**
   ("100 seleccionadas") en lugar de pintar todos los chips; quizá mostrar solo las primeras N + "ver
   todas".
2. **Acciones masivas:** "Seleccionar todas", "Limpiar", y selección **por sede** (con multi-sede,
   ADR-0009) para no marcar de a una.
3. **Repensar el modelo de asignación** (mayor alcance): asignar el formato **por sede/área** en vez
   de por dependencia individual, o invertir el flujo (asignar formatos desde la dependencia). Esto
   reduce la cardinalidad del control.

## Consecuencias
- ➕ El formulario vuelve a ser usable con cientos de dependencias.
- ➕ Menos fricción para asignaciones amplias (seleccionar por sede / todas).
- ➖ La opción 3 (cambiar el modelo) implica migración y tocar la generación de PDF (qué formato
  aplica a un evento); evaluar aparte.
- 🔁 Coordinar con multi-sede ([[adr-0009-migracion-multi-sede-progresiva]]).

## Alternativas consideradas
- **Mantener el multi-select actual:** simple pero ya demostró no escalar visualmente.
- **Paginar/virtualizar solo el dropdown:** ayuda a elegir, pero no resuelve el muro de chips de las
  ya seleccionadas (que es lo que rompe el modal).

## Implementación (2026-06-24)
**Layout del modal de formato** (`components/formats/form-modal.blade.php`): pasó de una sola columna
vertical a **dos columnas en escritorio** (`sm:grid-cols-2`, modal `max-w-2xl`): Nombre y slug lado a
lado; **Plantilla PDF** y **Dependencias asignadas** a ancho completo (`sm:col-span-2`). En móvil
sigue en una columna. Se quitó el aviso "debe coincidir con la clave en el archivo de configuración"
(ruido para el usuario) y se ajustaron paddings/gaps verticales (`py-4`, `gap-y-3`) para bajar la altura.

Y se mejoró el componente **compartido** `x-ui.multi-searchable-select` (beneficia también al modal de
usuarios), sin tocar el modelo de datos:
- **Chips acotados que llenan el ancho:** las seleccionadas viven en una **caja delimitada** (borde
  + fondo, `max-h-28 overflow-y-auto`) en **rejilla `grid-cols-1 sm:grid-cols-2`** con celdas que se
  **estiran a todo el ancho** (chip `w-full justify-between`, etiqueta `truncate`) → aprovecha el
  espacio en vez de chips angostos, y la altura del modal queda **constante** (5 o 100). Se prefirió
  esta caja con scroll vertical sobre scroll horizontal (esconde chips) o paginación (añade clics).
- **Contador + "Quitar todas":** encabezado con el número de seleccionadas y un botón para limpiar.
- **Acciones masivas:** en el panel, "Agregar todas" (o "Agregar filtradas" cuando hay búsqueda) —
  p. ej. buscar "Maicao" y agregar todas las coincidentes de un clic. Nuevos métodos
  `addAllAvailable()` / `clearAll()` en `resources/js/components/searchable-select.js`.
- **Verificación:** `UserEditModalToggleTest` (renderiza el multi-select) en verde + `npm run build`.

> La **opción 3** (repensar el modelo: asignar por sede/área o invertir el flujo) queda **diferida**
> como mejora mayor; lo implementado ya hace el formulario usable con cientos de dependencias.

## Pendiente para aceptar
- [x] Alcance elegido: UX del control (opciones 1-2). Opción 3 diferida.
- [x] Rama sugerida: `feat/formatos-dependencias-ux` (🟢 solo UI).

## Relacionado
[[adr-0015-mapeo-de-formatos-fuente-de-verdad-en-bd]] · [[adr-0017-pdf-de-formato-en-bd]] ·
[[adr-0009-migracion-multi-sede-progresiva]] · [[mapa-de-modulos]]
