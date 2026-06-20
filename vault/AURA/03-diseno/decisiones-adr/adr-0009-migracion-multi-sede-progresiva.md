---
tipo: adr
descripcion: ADR-0009 (aceptada) - Migracion multi-sede progresiva sin global scopes
actualizado: 2026-06-20
---

# ADR-0009 · Migracion multi-sede progresiva

- **Estado:** 🟢 Aceptada
- **Fecha:** 2026-06-20
- **Contexto del repo:** `campuses`, `campus_id` en tablas nucleo, `CampusScopeService`,
  `DashboardController`, endpoints de calendario en `routes/api.php`.

## Contexto
El sistema esta en produccion y ya tiene datos. Se agregan sedes para Maicao, Riohacha,
Fonseca y Villanueva, con roles `user`, `admin` y `superadmin`.

La migracion debe ser segura: no usar `migrate:fresh`, no borrar datos, no volver columnas
`NOT NULL` hasta completar backfills y auditoria por modulo. Ademas, existen rutas publicas
de QR que no deben romperse.

## Decision
Implementar multi-sede de forma progresiva:

- Crear `campuses` y poblarla con seeder idempotente.
- Agregar `campus_id` nullable a tablas nucleo.
- Poblar `campus_id` con backfills idempotentes y conteos.
- Centralizar la resolucion de sede en `CampusScopeService`.
- Aplicar filtros modulo por modulo, no con global scopes automaticos.
- Mantener `participants` y `formats` como datos globales.
- Usar sesion para la sede activa de `superadmin`.
- Registrar en [[migracion-multi-sede]] el estado de implementacion por modulo.

## Consecuencias
- ➕ Menor riesgo en produccion: cada modulo se migra y prueba por separado.
- ➕ Las rutas publicas pueden mantenerse sin filtro de sede directo.
- ➕ El servicio central reduce duplicacion de reglas.
- ➖ Mientras la matriz no este completa, algunos modulos pueden mezclar sedes.
- ➖ Hay que revisar cada query de eventos, administracion, calendario, estadisticas e imports.
- 🔁 Al final de la migracion, evaluar si `campus_id` puede pasar a `NOT NULL` en tablas nucleo.

## Alternativas consideradas
- **Global scopes por modelo** — descartado por riesgo de romper rutas publicas, backfills,
  seeders, imports y consultas administrativas globales.
- **Big bang multi-sede en todos los modulos** — descartado por riesgo operacional en produccion.
- **Mantener solo roles sin sede** — no cumple la necesidad de separar Maicao/Riohacha/Fonseca/Villanueva.

## Pendiente de seguimiento
- [ ] Eventos CRUD y `EventService`.
- [ ] Administracion de dependencias, areas y programas.
- [ ] Estadisticas y comparador de eventos.
- [ ] Imports/exports.
- [ ] Activity logs si se decide filtrar auditoria por sede.
- [ ] Evaluar `NOT NULL` cuando no queden registros sin sede.

## Relacionado
[[migracion-multi-sede]] · [[modelo-de-datos]] · [[mapa-de-modulos]] · [[arquitectura]]
