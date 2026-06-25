---
tipo: metodologia
descripcion: Las reglas de oro para que varias personas e IAs trabajen sin romper nada
actualizado: 2026-06-20
---

# Reglas de oro (multi-IA / multi-persona)

Este proyecto lo tocan varias personas **y** varias IAs. Estas reglas existen para que el
trabajo en paralelo no rompa nada. Si solo lees una nota de metodología, que sea esta.

## Las 10 reglas

1. **Reserva antes de tocar.** Anota tu tarea en [[tablero-trabajo-en-curso]] *antes* de
   escribir código. Si tu tarea no está en el tablero, no existe.
2. **Una rama por tarea**, nombrada según [[convencion-de-ramas]]. Nunca trabajes directo
   sobre `develop` ni `master`.
3. **Sugiere el nombre de la rama por adelantado** y márcala 🟢 paralela / 🔴 serializa según
   si toca el esquema. Ver [[nombres-de-rama-sugeridos]].
4. **El esquema (migraciones) serializa.** Si tu cambio crea/altera migraciones, es 🔴: avisa
   en el tablero y no lo solapes con otra rama que también toque el esquema.
5. **No edites código de la tarea de otro** sin coordinar en el tablero.
6. **Fiel al código.** Las notas `01-estado-actual` deben reflejar la realidad; si cambias el
   comportamiento, actualiza la nota o márcala desactualizada en el mismo PR.
7. **Decisiones con consecuencias → ADR** ([[plantilla-adr]]), no comentarios sueltos. Estado
   🟡 propuesta → 🟢 aceptada.
8. **Las convenciones vinculantes viven en `CLAUDE.md`.** El vault las **resume y enlaza**
   ([[convenciones]]); no las dupliques con versiones divergentes.
9. **Verde antes de PR.** Suite de tests pasando + `./vendor/bin/pint`. Ver
   [[convenciones-de-pruebas]].
10. **Commits pequeños y descriptivos** ([[convencion-de-commits]]). Un PR = una intención.

## Para una IA que entra fría
1. Lee [[Inicio]] → [[mapa-de-modulos]] → [[brechas-conocidas]].
2. Mira [[tablero-trabajo-en-curso]] para no chocar.
3. Propón tu rama ([[nombres-de-rama-sugeridos]]) y reserva la tarea.
4. Trabaja, prueba, actualiza el vault si cambió la realidad, abre PR.
