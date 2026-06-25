---
tipo: calidad
descripcion: Cómo escribimos los tests en este proyecto
actualizado: 2026-06-20
---

# Convenciones de pruebas

## Stack y comandos
- **PHPUnit** `^11.5` (no Pest, aunque el plugin está permitido en `composer.json`).
- Ejecutar: `composer run test` (limpia config + `php artisan test`) o `php artisan test`.
- Un solo archivo: `php artisan test --filter NombreDelTest`.

## Estructura
- `tests/Feature/` — la mayoría; prueban rutas/flujos con la app levantada.
- `tests/Unit/` — lógica aislada (p.ej. `Models/UserModelTest`).
- Agrupar por dominio en subcarpetas (`Auth/`, `Statistics/`, `Users/`, `Administration/`,
  `Dashboard/`, `Event/`, `Configuration/`).

## Patrones del repo
- Base de datos de test: SQLite (refrescar con `RefreshDatabase` según el caso).
- Reutilizar **escenarios** compartidos vía traits/concerns, como
  `tests/Feature/Statistics/Concerns/HasStatisticsScenario.php`, en vez de duplicar seeds.
- Nombres descriptivos en español del comportamiento esperado.
- Cubrir **permisos** explícitamente (admin vs. usuario vs. invitado) — es transversal aquí.

## Qué probar primero (prioridad)
1. Reglas de negocio críticas: registro de asistencia, coherencia de estadísticas, permisos.
2. Importaciones masivas (datos sucios → resultados esperados + descartados).
3. Rendimiento de rutas clave (ya hay `*PerformanceTest`).

## Definición de "hecho" para un cambio
- [ ] Test que cubra el camino feliz y al menos un error/permiso.
- [ ] `./vendor/bin/pint` sin cambios pendientes.
- [ ] Suite verde local antes de abrir PR.

> Qué está cubierto y qué falta: [[estrategia-de-pruebas]].
