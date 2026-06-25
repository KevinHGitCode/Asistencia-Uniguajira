---
tipo: calidad
descripcion: Qué se prueba hoy y dónde están los huecos de cobertura
actualizado: 2026-06-20
---

# Estrategia de pruebas

Estado real de `tests/` (PHPUnit ^11.5, `php artisan test` / `composer run test`).

## Qué hay cubierto (Feature)
- **Auth**: autenticación, verificación email, reset, confirmación, registro.
- **Usuarios**: index, create, update, delete, show.
- **Dashboard**: acceso, calendario, saludo, stats.
- **Estadísticas**: acceso, asistencias, participantes, usuarios, filtros, **coherencia**,
  rendimiento (`StatisticsPerformanceTest`). Escenario compartido en
  `tests/Feature/Statistics/Concerns/HasStatisticsScenario.php`.
- **Administración**: paginación de tablas, `DependencyTableTest`.
- **Eventos**: `CreateEventWizardTest`.
- **Importación**: `ProgramImportTest`.
- **Rendimiento de rutas**: `PagePerformanceTest`, `WebRoutesPerformanceTest`,
  `ApiRoutesPerformanceTest`.
- **Relaciones**: `ParticipantRelationsTest`.
- **Unit**: `Models/UserModelTest`.

## Huecos conocidos (candidatos)
- **Registro público de asistencia**: el flujo crítico [[registro-de-asistencia]] no tiene
  test de feature propio del componente Livewire (anti-duplicado, comunidad externa, detalle).
- **PDF de asistencia** (`AttendancePdfService`) y **mapper de formatos**: sin pruebas.
- **Auditoría** (`ActivityLogService`): sin pruebas dedicadas.
- Placeholders `ExampleTest` (Unit + Feature) por limpiar ([[brechas-conocidas]] #7).

## Principios
- Priorizar tests de **feature** sobre los flujos de dinero/datos críticos (registro,
  estadísticas coherentes, permisos).
- Mantener el **escenario de estadísticas** como fuente de datos reproducible.
- Cómo escribirlos: [[convenciones-de-pruebas]].
