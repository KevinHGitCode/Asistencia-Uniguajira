---
tipo: adr
descripcion: ADR-0029 (implementado) — Navegación semestral del calendario habilitada solo para periodos con eventos visibles
actualizado: 2026-07-02
---

# ADR-0029 · Navegación por semestre en el calendario

- **Estado:** 🟢 Implementado
- **Fecha:** 2026-06-25
- **Fecha de implementación:** 2026-07-02
- **Contexto del repo:** módulo de calendario (`resources/views/calendar/`, vista de calendario del
  dashboard; animación CSS del indicador "Hoy" — ver [[mapa-de-modulos]] y `CLAUDE.md`).

## Contexto
El año académico se organiza por **semestres**. Cerca de un cambio de semestre (p. ej. fin de junio)
es útil mirar el **semestre anterior** o adelantarse al **siguiente**, pero hoy el calendario no
ofrece un salto directo entre periodos académicos.

## Decisión
Agregar controles para **retroceder al semestre anterior** y **avanzar al siguiente**, con una
**condición**: el control solo se habilita si en ese semestre **hay eventos registrados**. Así no se
navega a periodos vacíos.

- Definir el semestre (p. ej. **I = ene–jun**, **II = jul–dic**) de forma configurable.
- Habilitar "anterior"/"siguiente" según `exists` de eventos en ese rango (respetando el filtro por
  sede vigente, [[migracion-multi-sede]]).
- La navegación salta al **inicio del semestre destino**, no al primer mes con eventos, para mantener
  estable la vista semestral completa.

## Implementación
Implementado en el dashboard/calendario mediante configuración de periodos académicos y metadatos de
navegación expuestos por `/api/eventos-json?include_navigation=1`. El endpoint conserva la respuesta
legacy como array cuando no se solicitan metadatos de navegación.

- Configuración: `config/academic.php`.
- Servicio de rangos y periodos: `App\Services\AcademicSemesterService`.
- UI: botones anterior/siguiente en el header del calendario del dashboard.
- Pruebas: `tests/Feature/Dashboard/DashboardCalendarTest.php`.

## Consecuencias
- ➕ Navegación contextual por periodo académico, sin caer en meses vacíos.
- ➕ Útil justo en los cambios de semestre.
- ➖ Definir y mantener los **límites de semestre** (configurable) y consultas de existencia por rango.
- ✅ La navegación queda restringida al semestre anterior/siguiente inmediato y solo se activa si ese
  periodo tiene eventos visibles para el usuario/sede vigente.

## Alternativas consideradas
- **Solo navegación mensual (actual)** — funciona pero es lenta para saltar de periodo.
- **Mostrar siempre los botones** (sin condición de eventos) — llevaría a vistas vacías.

## Relacionado
[[mapa-de-modulos]] · [[migracion-multi-sede]] · [[modelo-de-datos]]
