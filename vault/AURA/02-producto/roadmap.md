---
tipo: producto
descripcion: Dirección del producto — hecho, en curso y candidatos, derivados del estado real
actualizado: 2026-06-20
---

# Roadmap

Vivo. Derivado del estado real ([[mapa-de-modulos]]) y las [[brechas-conocidas]]. No es un
compromiso de fechas; es orden de prioridad sugerido.

## ✅ Hecho (base sólida)
- Eventos + QR público + registro rico de asistencia ([[registro-de-asistencia]]).
- PDF por formato de dependencia con mapper visual.
- Administración completa (dependencias, áreas, programas, afiliaciones, estamentos,
  organizaciones, importación, auditoría) con import/export Excel y paginación.
- Estadísticas con React + API y comparador de eventos.

## 🔧 Deuda / saneamiento (recomendado primero)
- Retirar el **flujo legacy** de asistencia → [[adr-0003-retirar-flujo-legacy-de-asistencia]].
- Decidir auth de los endpoints `/api/statistics/*` individuales (brecha #4).
- Actualizar `README.md` y la tabla de relaciones de `CLAUDE.md` (brechas #2 y #3).
- Activar/ordenar seeders de demo (brecha #5).

## 🚀 Candidatos de producto (sin compromiso)
- Notificaciones reales (la UI hoy solo manda correo de confirmación).
- Auto-registro de participantes internos (hoy el externo sí, el interno depende de import).
- Exportación de estadísticas (PDF/Excel) desde el dashboard.
- Roles más finos por dependencia.

> Ideas sin madurar van a [[ideas]]. Cuando una idea implica una decisión con consecuencias,
> se promueve a un ADR en `03-diseno/decisiones-adr/`.
