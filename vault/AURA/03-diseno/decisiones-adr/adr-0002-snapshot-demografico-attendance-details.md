---
tipo: adr
descripcion: ADR-0002 — Capturar la demografía como snapshot por asistencia en attendance_details
actualizado: 2026-06-20
---

# ADR-0002 · Snapshot demográfico en `attendance_details`

- **Estado:** 🟢 Aceptada
- **Fecha:** 2026-06-20 (registrada; decisión evidenciada en migraciones y componente).
- **Contexto del repo:** migración `2026_03_13_..._create_attendance_details_table`,
  `app/Livewire/Event/AttendanceRegistration.php`.

## Contexto
Un participante puede tener **varios roles** a lo largo del tiempo (cambia de programa,
estamento, dependencia) — ver `participant_roles` en [[modelo-de-datos]]. Si las estadísticas
demográficas se derivaran del estado **actual** del participante, los reportes históricos
cambiarían retroactivamente al editar su perfil.

## Decisión
Guardar la demografía **en el momento del registro** en `attendance_details` (género,
teléfono, ciudad, barrio, dirección, grupo priorizado) junto con el `participant_role_id`
elegido para esa asistencia. Las estadísticas y los PDF leen este detalle, no el estado
vigente del participante.

## Consecuencias
- ➕ Reportes históricamente **fieles**: una asistencia de 2025 conserva sus datos de 2025.
- ➕ Permite estadísticas por género/grupo priorizado/dependencia/organización por evento.
- ➖ Cierta **redundancia** (se repite demografía en cada asistencia); se mitiga precargando
  el último detalle (`loadLastDefaults`).
- 🔁 Las consultas de estadística unen `attendances → attendance_details → participant_roles`.

## Alternativas consideradas
- Derivar todo de `participant_roles` (estado actual): simple pero **no fiel** en el tiempo.
- Versionar el participante completo: mayor complejidad de la necesaria.

## Relacionado
[[modelo-de-datos]] · [[registro-de-asistencia]] · [[mapa-de-modulos]]
