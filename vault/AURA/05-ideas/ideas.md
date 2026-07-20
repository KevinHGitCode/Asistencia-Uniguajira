---
tipo: idea
descripcion: Backlog de ideas y exploraciones sin compromiso
actualizado: 2026-06-20
---

# Ideas

Espacio de bajo compromiso. Una idea que madura y tiene consecuencias se promueve a un ADR
([[plantilla-adr]]). Lo que ya es plan priorizado vive en [[roadmap]].

## Derivadas de brechas reales ([[brechas-conocidas]])
- **Retirar flujo legacy de asistencia** → ya formalizada como propuesta
  [[adr-0003-retirar-flujo-legacy-de-asistencia]].
- **Proteger `/api/statistics/*` individuales** con auth o documentar por qué son públicos.
- **Reescribir el README** con el stack real y los módulos.
- **Ordenar seeders de demo** para un entorno reproducible.

## Producto
- Exportar el dashboard de estadísticas a PDF/Excel.
- Notificaciones reales (recordatorio de evento, resumen post-evento al organizador).
- Auto-registro de participantes internos en el QR (hoy depende de importación previa).
- Vista pública de "mi historial de asistencias" por documento.
- Roles más finos por dependencia (coordinador vs. gestor).

## Técnicas
- Tests de feature del componente `AttendanceRegistration`.
- Caché de agregados de estadísticas para eventos grandes.
- Unificar el estilo de UI (reducir la convivencia Blade/Alpine/React donde no aporte).
- **Guía portable del módulo de banners** (pedida por Kevin, 2026-07-19): un Markdown
  autocontenido que explique cómo replicar el módulo de anuncios ([[adr-0030-banner-de-anuncios-en-registro-publico]]
  + [[adr-0031-estadisticas-diarias-de-banners]]) en otros proyectos Laravel: esquema,
  rutas, parcial público con `sendBeacon`, reporte y decisiones (imagen en BD, rotación
  ponderada). Sirve también para anuncios internos, no solo patrocinadores.

## Navegación (derivadas de ADR-0013)
- **Origen calendario en breadcrumb**: `EventController::getByDate` (L307) genera `show_url`
  sin `?from`. Abrir un evento desde el calendario da "Eventos → Información". Con `?from=calendario`
  podría mostrar "Inicio → Calendario → Información". Pequeño cambio: añadir `from=calendario`
  en `getByDate` y un cuarto `if` en `resolveBreadcrumb`.
- **Redirección post-eliminar pierde contexto**: `EventController::destroy` hace
  `redirect()->route('events.list')` independientemente del origen. Si venías del detalle de
  un usuario y eliminas, te lleva a "Tus eventos". Solución: pasar `from` y `user_id` como
  campos ocultos en `x-events.delete-modal` y usar `request('from')` en `destroy` para redirigir
  correctamente. Baja prioridad, impacto percibido mediano.

> Formato sugerido por idea: una línea de qué + por qué. Si crece, dale su propia nota.
