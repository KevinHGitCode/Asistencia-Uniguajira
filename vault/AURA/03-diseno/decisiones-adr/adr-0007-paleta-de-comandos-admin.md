---
tipo: adr
descripcion: ADR-0007 (propuesta) — Paleta de comandos (command palette) para navegación rápida de administradores
actualizado: 2026-06-20
---

# ADR-0007 · Paleta de comandos para administradores

- **Estado:** 🟢 Implementado (MVP)
- **Fecha:** 2026-06-20
- **Implementado:** 2026-06-21
- **Contexto del repo:** sidebar con rutas de módulos en
  `resources/views/components/layouts/app/sidebar.blade.php`; rol admin (`role:admin`);
  componente de búsqueda con teclado ya construido en `resources/js/components/searchable-select.js`.

## Contexto
Un administrador navega entre muchos módulos (eventos, usuarios, dependencias, programas,
formatos, estamentos, afiliaciones, organizaciones, participantes, registros, estadísticas). Hoy
solo puede llegar por el sidebar. Una **paleta de comandos** estilo “Ctrl/Cmd+K” permite saltar a
cualquier módulo (o ejecutar acciones predefinidas) escribiendo, sin tocar el mouse.

## Decisión propuesta
Añadir una **paleta de comandos global, solo para admins**:

- **Atajo:** `Ctrl/Cmd + K` (explícitamente **no `Ctrl+P`**, reservado a imprimir del navegador;
  `Cmd/Ctrl+K` es el estándar de facto y no choca con atajos críticos). Soportar también un
  segundo disparador accesible (botón en el header).
- **Contenido:** un **registro de comandos predefinidos** — “Ir a Participantes”, “Ir a Eventos”,
  “Nuevo evento”, “Registros de actividad”, etc. — con `wire:navigate` para navegación SPA.
- **UX:** input con filtrado sin acentos, navegación con ↑/↓ y Enter, resaltado y scroll que
  acompaña — **reutilizando la lógica ya existente** en
  `resources/js/components/searchable-select.js` (extraer el núcleo de filtrado/teclado).
- **Gate:** solo se monta/activa si `auth()->user()->role === 'admin'`.
- **Implementación:** isla ligera (Alpine, o un componente Livewire pequeño) montada en el layout.

## Consecuencias
- ➕ Navegación muy rápida para power users/admins.
- ➕ Reutiliza código ya escrito (filtrado + teclado del selector con búsqueda).
- ➖ Hay que **mantener el registro de comandos** al alza con los módulos.
- ➖ Cuidar accesibilidad (focus trap, `aria`, Escape) y no capturar el atajo cuando el foco está
  en un input de texto si interfiere.
- 🔁 Cambio aditivo en el layout; sin esquema.

## Alternativas consideradas
- **Solo mejorar el sidebar** (búsqueda dentro del nav): menos potente, sin acciones.
- **Usar `Ctrl+P`**: descartado, colisiona con imprimir del navegador (lo pidió el usuario).

## Implementación (MVP)

- **Rama:** `feat/paleta-comandos-admin`.
- **Núcleo de filtrado reutilizado:** se extrajo `normalizar` + `filtrarOpciones` a
  [`resources/js/components/text-filter.js`](../../../../resources/js/components/text-filter.js);
  `searchable-select.js` ahora lo importa (sin duplicar) y `command-palette.js` lo reutiliza.
- **Isla Alpine:** [`resources/js/components/command-palette.js`](../../../../resources/js/components/command-palette.js)
  (`commandPalette`): atajo global `Cmd/Ctrl+K`, ↑/↓, Enter, Esc, scroll que acompaña; navega con
  `Livewire.navigate` (SPA) y cae a recarga si no está disponible.
- **Registro de comandos + modal:** [`resources/views/components/command-palette.blade.php`](../../../../resources/views/components/command-palette.blade.php).
  Los comandos se construyen en PHP con `route()` y se filtran por rol (el de **Sedes** solo para
  superadmin). Se monta una sola vez en `app/sidebar.blade.php`, gated con `hasAdminAccess()`,
  vía `<x-command-palette />` teletransportada al `body`.
- **Segundo disparador:** botón "Buscar… ⌘K" bajo el logo del sidebar (solo admins) que emite el
  evento `open-command-palette`.
- **Gate:** se usó `hasAdminAccess()` (admin **+** superadmin) en lugar del `role === 'admin'`
  literal del ADR, por coherencia con el resto del sidebar.
- **Tests:** [`tests/Feature/CommandPaletteTest.php`](../../../../tests/Feature/CommandPaletteTest.php)
  — la paleta se monta solo para admins; el comando de Sedes solo para superadmin.

### Comandos incluidos
Inicio · Nuevo evento · Tus eventos · Todos los eventos · Estadísticas (+ Asistencias /
Participantes / Compara eventos / Usuarios) · Usuarios · Administración · Sedes (superadmin) ·
Dependencias · Programas · Formatos · Estamentos · Afiliaciones · Organizaciones · Participantes ·
Registros de actividad.

### Pendiente / mejoras futuras (fuera del MVP)
- Acciones contextuales además de navegación (p. ej. "Nuevo usuario", "Descargar PDF del evento N").
- Foco-trap completo y `aria-activedescendant` para accesibilidad fina.
- Resaltado del término coincidente en cada resultado.

## Relacionado
[[arquitectura]] · [[personas-y-roles]] · [[adr-0008-listado-participantes-en-react]]
