# AURA — Banco de conocimiento de Asistencia Uniguajira

**AURA** es el *vault* (segundo cerebro) del proyecto **Asistencia Uniguajira**. Aquí vive
el **conocimiento sobre el sistema**: visión de producto, estado real del código,
arquitectura, decisiones (ADR), calidad y la metodología para trabajar entre varias
personas e IAs sin romper nada.

> ℹ️ *AURA* también es la marca pública de la aplicación (logo en la página de registro
> de asistencia). En este repositorio, `vault/AURA/` se refiere **al conocimiento**, no a
> la app. La app es el código en la raíz del repo; AURA es lo que sabemos sobre ella.

## Qué NO es esto

- No reemplaza a `CLAUDE.md` ni al código. Las **convenciones de código vinculantes**
  viven en `CLAUDE.md` (raíz del repo); aquí solo se **resumen y enlazan**.
- No es documentación autogenerada: es conocimiento curado, una nota = una idea.

## Cómo abrirlo en Obsidian

1. Instala [Obsidian](https://obsidian.md).
2. *Open folder as vault* → selecciona la carpeta `vault/AURA`.
3. Empieza por [[Inicio]] (el mapa de contenido / MOC).

> No hace falta Obsidian para leerlo: son archivos Markdown. Pero los `[[wikilinks]]` y el
> grafo se navegan mejor en Obsidian.

## Convenciones del vault

- **Una nota = una idea.** Se enlaza con `[[wikilinks]]` en vez de copiar contenido.
- **Nombres de archivo en ASCII sin acentos** (rutas seguras en Windows/git). Los acentos
  van en el **contenido** y en los **títulos** (`# Título`).
- **Frontmatter** en cada nota: `tipo`, `descripcion`, `actualizado` (YYYY-MM-DD).
- **Estado actual fiel al código.** Si el código cambia, se actualiza la nota o se marca
  como desactualizada.
- **Decisiones con consecuencias → ADR** con estado: 🟡 propuesta · 🟢 aceptada · 🔴 rechazada · ⚪ obsoleta.

## Estructura

| Carpeta | Contenido |
|---|---|
| `01-estado-actual` | Foto fiel del repo: stack, datos, módulos, brechas |
| `02-producto` | Visión, personas/roles, roadmap, módulos, historias de usuario, casos de uso |
| `03-diseno` | Arquitectura, convenciones, decisiones (ADR) |
| `04-calidad` | Estrategia y convenciones de pruebas |
| `05-ideas` | Ideas y exploraciones sin compromiso |
| `06-negocio` | Contexto, actores y valor para la universidad |
| `07-metodologia` | Cómo trabajamos varias personas e IAs sin romper nada |
| `99-plantillas` | Plantillas para crear notas nuevas |
