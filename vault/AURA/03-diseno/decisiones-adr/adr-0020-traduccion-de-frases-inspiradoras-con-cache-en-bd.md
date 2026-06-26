---
tipo: adr
descripcion: ADR-0020 (propuesta) — Traducir las frases inspiradoras vía API y cachearlas en BD como catálogo propio
actualizado: 2026-06-25
---

# ADR-0020 · Traducción de frases inspiradoras con caché en BD

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-25
- **Contexto del repo:** `resources/views/components/layouts/auth/split.blade.php` (líneas ~136-145)
  muestra una cita aleatoria con `Illuminate\Foundation\Inspiring::quotes()->random()` — **en inglés**.

## Contexto
La pantalla de login muestra una **frase inspiradora** de Laravel (`Inspiring::quotes()`), pero está
en **inglés** y la UI del producto es en **español** ([[convenciones]]). Traducir con una API en cada
carga sería lento, costoso y dependería de que la API siga viva.

## Decisión
Traducir cada frase **una sola vez** y **guardarla en BD** como catálogo propio:

1. Tabla `inspiring_quotes` (texto original, autor, **traducción**, idioma destino, hash del
   original para búsqueda rápida).
2. Al necesitar una frase: si su traducción ya está en BD, **se usa esa** (sin llamar a la API). Si
   no, se traduce con la **API** (p. ej. configurable vía `.env`), se **guarda** y se muestra.
3. Con el tiempo, el catálogo de frases traducidas se **completa solo**; la app puede mostrar frases
   en español **sin la API**.

## Consecuencias
- ➕ **Independencia de la API**: cuando se desactive, la app ya tiene su **banco de frases** en BD.
- ➕ Rápido y barato: cada frase se traduce **una vez**.
- ➕ Coherencia: login en español como el resto.
- ➖ Nueva tabla + integración con un proveedor de traducción (clave en `.env`).
- 🔁 Decidir proveedor (DeepL/Google/LibreTranslate) y un *fallback* (mostrar original si la API
  falla y aún no hay traducción).

## Alternativas consideradas
- **Traducir en cada carga** — lento, costoso y frágil; descartada.
- **Traducir a mano y hardcodear** — no escala con el set de frases; descartada (aunque se puede
  *sembrar* el catálogo con algunas ya traducidas).
- **Mantenerlas en inglés** — inconsistente con la UI.

## Pendiente para aceptar
- [ ] Proveedor de traducción y modelo de costo.
- [ ] ¿Frase fija por sesión/día o aleatoria en cada carga?

## Relacionado
[[convenciones]] · [[stack-tecnologico]] · [[modelo-de-datos]]
