---
tipo: producto
descripcion: Direccion del producto - hecho, en curso y candidatos, derivados del estado real
actualizado: 2026-06-20
---

# Roadmap

Vivo. Derivado del estado real ([[mapa-de-modulos]]) y las [[brechas-conocidas]]. No es un
compromiso de fechas; es orden de prioridad sugerido.

## ✅ Hecho (base solida)
- Eventos + QR publico + registro rico de asistencia ([[registro-de-asistencia]]).
- PDF por formato de dependencia con mapper visual.
- Administracion completa con import/export Excel y paginacion.
- Estadisticas con React + API y comparador de eventos.
- Base estructural multi-sede:
  - `campuses`,
  - `campus_id` nullable en tablas nucleo,
  - roles `user/admin/superadmin`,
  - `CampusScopeService`,
  - backfills idempotentes,
  - `academic_programs`.
- Dashboard + calendario ya filtran por sede.

## 🔧 Deuda / saneamiento recomendado primero
- Completar migracion multi-sede modulo por modulo -> [[migracion-multi-sede]].
- ~~Retirar el flujo legacy de asistencia~~ ✅ hecho 2026-06-24 -> [[adr-0003-retirar-flujo-legacy-de-asistencia]].
- Decidir auth y filtro por sede de endpoints `/api/statistics/*`.
- Actualizar `README.md` y `CLAUDE.md`.
- Activar/ordenar seeders de demo.

## 🧭 Planeado (propuestas activas - 2026-06-20)
Iniciativas formalizadas como ADR o seguimiento vivo. Ramas sugeridas en
[[nombres-de-rama-sugeridos]].

- **Migracion multi-sede progresiva** -> [[adr-0009-migracion-multi-sede-progresiva]] y
  [[migracion-multi-sede]].
- **Pasarela de revision para importar participantes** -> [[adr-0004-pasarela-de-revision-para-importacion-de-participantes]].
- **Rate limiting anti-abuso** -> [[adr-0005-rate-limiting-anti-abuso]].
- **Formularios en modal centrado** -> [[adr-0006-formularios-en-modal-centrado]].
- **Paleta de comandos para administradores** -> [[adr-0007-paleta-de-comandos-admin]].
- ~~**Listado de participantes en React**~~ ✅ hecho 2026-06-24 (Opción A) -> [[adr-0008-listado-participantes-en-react]].
- ~~**Mejoras al modulo de usuarios**~~ ✅ frentes 2 y 3 hechos 2026-06-24 (activos en vivo + estadisticas de uso) -> [[adr-0010-mejoras-modulo-usuarios]].
- ~~**Mejores filtros en participantes**~~ ✅ hecho 2026-06-24 -> [[adr-0011-mejores-filtros-en-participantes]].
- **Busqueda y filtros en eventos** -> [[adr-0012-busqueda-y-filtros-en-eventos]].
- **Breadcrumbs correctos en detalle de evento** -> [[adr-0013-breadcrumbs-detalle-evento]].
- **Proteger/retirar rutas API de prueba sin auth** -> [[adr-0014-proteger-rutas-api-de-prueba-sin-auth]].

## 🚀 Candidatos de producto
- Notificaciones reales.
- Auto-registro de participantes internos.
- Exportacion de estadisticas (PDF/Excel) desde el dashboard.
- Roles mas finos por dependencia.

> Ideas sin madurar van a [[ideas]]. Cuando una idea implica una decision con consecuencias,
> se promueve a un ADR en `03-diseno/decisiones-adr/`.
