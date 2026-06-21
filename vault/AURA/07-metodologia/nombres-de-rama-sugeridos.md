---
tipo: metodologia
descripcion: Catálogo de ramas sugeridas por funcionalidad, con marca paralela/serializa
actualizado: 2026-06-20
---

# Nombres de rama sugeridos

Práctica: **proponer el nombre de la rama por adelantado** para cada funcionalidad, marcando
si puede ir en paralelo (🟢) o si debe serializarse por tocar esquema/contratos (🔴). Formato
y prefijos en [[convencion-de-ramas]]. Reserva la tarea en [[tablero-trabajo-en-curso]].

## Cómo decidir 🟢 vs. 🔴
- **🔴 serializa** si la rama: crea/altera **migraciones**, cambia **rutas públicas** o
  contratos de API que otros consumen, o toca `config/attendance_formats.php`.
- **🟢 paralela** en el resto (UI, lógica interna aislada, docs/vault, tests).

## Catálogo (derivado de [[roadmap]] y [[brechas-conocidas]])

| Funcionalidad | Rama sugerida | 🟢/🔴 | Por qué |
|---|---|---|---|
| Retirar flujo legacy de asistencia | `refactor/retirar-asistencia-legacy` | 🔴 | Quita ruta pública POST ([[adr-0003-retirar-flujo-legacy-de-asistencia]]) |
| Proteger estadísticas con auth | `fix/auth-endpoints-estadisticas` | 🔴 | Cambia contrato de `/api/statistics/*` |
| Actualizar README al stack real | `docs/actualizar-readme-stack` | 🟢 | Solo documentación |
| Corregir relaciones en CLAUDE.md | `docs/corregir-relaciones-claude-md` | 🟢 | Solo documentación |
| Ordenar seeders de demo | `chore/ordenar-seeders-demo` | 🔴 | Toca siembra/datos base |
| Exportar estadísticas a PDF/Excel | `feat/exportar-estadisticas` | 🟢 | Lectura + nueva vista, sin esquema |
| Tests del registro de asistencia | `test/cobertura-registro-asistencia` | 🟢 | Solo pruebas |
| Notificaciones reales | `feat/notificaciones-eventos` | 🔴 | Probable migración (tabla notifications/jobs) |
| Auto-registro de internos en QR | `feat/autoregistro-internos-qr` | 🟢 | Lógica del componente, sin esquema |
| Pasarela de revisión de importación | `feat/pasarela-importacion-participantes` | 🔴 | Crea tablas de staging ([[adr-0004-pasarela-de-revision-para-importacion-de-participantes]]) |
| Rate limiting de rutas | `feat/rate-limiting-rutas` | 🔴 | Cambia contratos de rutas (429) ([[adr-0005-rate-limiting-anti-abuso]]) |
| Formularios a modal centrado | `refactor/formularios-modal-centrado` | 🟢 | Solo UI ([[adr-0006-formularios-en-modal-centrado]]) |
| Paleta de comandos admin | `feat/paleta-comandos-admin` | 🟢 | UI/JS, sin esquema ([[adr-0007-paleta-de-comandos-admin]]) |
| Listado de participantes en React | `feat/participantes-listado-react` | 🔴 | Nuevo endpoint/contrato API ([[adr-0008-listado-participantes-en-react]]) |
| Mejoras al módulo de usuarios | `feat/modulo-usuarios-mejoras` | 🟢 | UI + lectura de sessions/logs ([[adr-0010-mejoras-modulo-usuarios]]) |
| Mejores filtros en participantes | `feat/participantes-filtros` | 🟢 | Lectura/UI (🔴 si añade índices) ([[adr-0011-mejores-filtros-en-participantes]]) |
| Búsqueda y filtros en eventos | `feat/eventos-busqueda-filtros` | 🟢 | Lectura/UI ([[adr-0012-busqueda-y-filtros-en-eventos]]) |
| Breadcrumbs detalle de evento | `fix/breadcrumbs-detalle-evento` | 🟢 | UI/navegación ([[adr-0013-breadcrumbs-detalle-evento]]) |

> Añade filas cuando propongas una funcionalidad nueva. Una funcionalidad sin rama propuesta
> aquí no debería empezar a codificarse (ver [[reglas-de-oro]]).
