---
tipo: adr
descripcion: ADR-0021 (propuesta) — El select buscable abre hacia arriba o abajo según el espacio disponible
actualizado: 2026-06-25
---

# ADR-0021 · Dirección adaptativa del desplegable buscable

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-25
- **Contexto del repo:** `resources/views/components/ui/searchable-select.blade.php` y
  `multi-searchable-select.blade.php` (componente de selección buscable, usado p. ej. en formatos).

## Contexto
El select buscable (lista grande con filtro) **siempre se despliega hacia abajo**. Cuando el campo
está cerca del **borde inferior** de la pantalla, la lista crece hacia abajo y queda **tapada** /
fuera de la vista, obligando a hacer scroll para ver y elegir opciones.

## Decisión
Hacer que el componente **decida la dirección de apertura** según el **espacio disponible**: si
debajo del campo no cabe la lista pero **arriba sí**, abre **hacia arriba**; en caso normal, hacia
abajo. Se calcula al abrir (y, si se quiere, al hacer scroll/resize) comparando el `boundingRect` del
campo contra el alto del viewport, con un alto máximo de lista y un pequeño margen.

## Consecuencias
- ➕ La lista **siempre queda visible**, sin importar dónde esté el campo en el formulario.
- ➕ Mejora la usabilidad de formularios largos (la queja concreta que origina este ADR).
- ➖ Lógica de posicionamiento en Alpine (medir y elegir dirección/clases).
- 🔁 Aplica a **ambos** componentes (`searchable-select` y `multi-searchable-select`).

## Alternativas consideradas
- **Siempre hacia abajo (actual)** — falla cerca del borde inferior.
- **Librería de *floating* (Floating UI/Popper)** — robusta pero añade dependencia; quizá excesiva
  para el caso. Se puede empezar con cálculo propio y migrar si hiciera falta.

## Relacionado
[[adr-0025-componente-select-personalizado]] · [[adr-0016-edicion-formato-muchas-dependencias]] ·
[[convenciones]]
