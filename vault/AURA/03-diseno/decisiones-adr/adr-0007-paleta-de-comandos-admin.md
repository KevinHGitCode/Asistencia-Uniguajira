---
tipo: adr
descripcion: ADR-0007 (propuesta) — Paleta de comandos (command palette) para navegación rápida de administradores
actualizado: 2026-06-20
---

# ADR-0007 · Paleta de comandos para administradores

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-20
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

## Pendiente para aceptar
- [ ] Lista inicial de comandos/acciones.
- [ ] Confirmar atajo (`Cmd/Ctrl+K`) y comportamiento dentro de inputs.
- [ ] Rama sugerida: `feat/paleta-comandos-admin` (🟢 UI/JS, sin esquema).

## Relacionado
[[arquitectura]] · [[personas-y-roles]] · [[adr-0008-listado-participantes-en-react]]
