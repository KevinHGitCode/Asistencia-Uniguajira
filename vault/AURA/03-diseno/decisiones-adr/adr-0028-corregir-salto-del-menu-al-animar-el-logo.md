---
tipo: adr
descripcion: ADR-0028 (propuesta) — Corregir el salto del menú de usuario cuando el logo AURA se anima al hover
actualizado: 2026-06-25
---

# ADR-0028 · Corregir el salto del menú al animar el logo AURA

- **Estado:** 🟡 Propuesta (corrección de bug visual)
- **Fecha:** 2026-06-25
- **Contexto del repo:** `resources/views/components/layouts/app/sidebar.blade.php` — regla
  `.aura-sidebar-link:hover .aura-logo-sidebar { transform: scale(1.18) rotate(-2deg); ... }` sobre el
  logo del sidebar; al pie del sidebar va el perfil del usuario (`flux:profile`).

## Contexto
Al pasar el mouse por el **logo AURA** del sidebar, este **crece e inclina** (animación deseada).
Pero como **efecto colateral**, el **ícono/menú de usuario** del pie del sidebar **se desplaza hacia
arriba** durante el hover. Es un **defecto visual**: el resto del layout no debería moverse.

## Decisión
Aislar la animación del logo para que **no afecte el flujo** del sidebar:
- Animar **solo con `transform`** (scale/rotate no deberían reflujo) y asegurar que el contenedor del
  logo **reserva su espacio** (alto/área fijos), de modo que el escalado no empuje a los hermanos.
- Usar `transform-origin` adecuado y, si hace falta, `will-change: transform` / aislar con
  `contain: layout paint` o un wrapper de tamaño fijo con `overflow` controlado.

## Consecuencias
- ➕ La animación del logo se conserva **sin mover** el menú de usuario ni el resto del sidebar.
- ➕ Sidebar visualmente estable (mejor pulido).
- ➖ Ajuste fino de CSS; verificar en distintos tamaños y en móvil.
- 🔁 Cambio acotado a la hoja de estilos del sidebar.

## Alternativas consideradas
- **Quitar la animación** — elimina el bug pero también el detalle agradable; innecesario.
- **Reducir el `scale`** — disimula el salto, no lo corrige de raíz.

## Relacionado
[[convenciones]] · [[mapa-de-modulos]]
