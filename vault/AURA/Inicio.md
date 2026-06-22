---
tipo: inicio
descripcion: Mapa de contenido (MOC) del vault AURA - puerta de entrada a todo el conocimiento
actualizado: 2026-06-20
---

# Inicio - AURA

Banco de conocimiento de **Asistencia Uniguajira**. Empieza aqui. Cada enlace lleva a una
nota; cada nota es una idea. Lee primero [[README]] si es tu primera vez.

> **Atajo mental:** que es hoy el sistema -> 01. Que queremos -> 02. Como esta construido -> 03.
> Como aseguramos que funciona -> 04. Como trabajamos juntos -> 07.

## 01 - Estado actual (fiel al codigo)
- [[stack-tecnologico]] - versiones reales de backend, frontend y datos
- [[modelo-de-datos]] - tablas, relaciones y piezas clave
- [[mapa-de-modulos]] - modulos y grado de implementacion
- [[migracion-multi-sede]] - matriz viva para revisar si cada modulo respeta sede
- [[brechas-conocidas]] - lo que la UI/docs prometen vs. lo que el backend entrega

## 02 - Producto
- [[vision]] - para que existe el sistema
- [[personas-y-roles]] - quien lo usa
- [[roadmap]] - hacia donde va
- [[modulos]] - indice de modulos del producto
- Historias de usuario -> [[hu-0001-registro-asistencia-qr]]
- Casos de uso -> [[cu-0001-descargar-pdf-asistencia]]

## 03 - Diseno
- [[arquitectura]] - como encajan las piezas
- [[convenciones]] - resumen de reglas de codigo
- Decisiones (ADR):
  - [[adr-0001-react-islands-estadisticas]] 🟢
  - [[adr-0002-snapshot-demografico-attendance-details]] 🟢
  - [[adr-0003-retirar-flujo-legacy-de-asistencia]] 🟡
  - [[adr-0004-pasarela-de-revision-para-importacion-de-participantes]] 🟢
  - [[adr-0005-rate-limiting-anti-abuso]] 🟢
  - [[adr-0006-formularios-en-modal-centrado]] 🟡
  - [[adr-0007-paleta-de-comandos-admin]] 🟡
  - [[adr-0008-listado-participantes-en-react]] 🟡
  - [[adr-0009-migracion-multi-sede-progresiva]] 🟢
  - [[adr-0010-mejoras-modulo-usuarios]] 🟡
  - [[adr-0011-mejores-filtros-en-participantes]] 🟡
  - [[adr-0012-busqueda-y-filtros-en-eventos]] 🟡
  - [[adr-0013-breadcrumbs-detalle-evento]] 🟢

## 04 - Calidad
- [[estrategia-de-pruebas]] - que probamos y por que
- [[convenciones-de-pruebas]] - como escribimos tests
- [[benchmark-importacion-participantes]] - referencia de rendimiento del cargue masivo

## 05 - Ideas
- [[ideas]] - backlog de ideas y exploraciones

## 06 - Negocio
- [[contexto-negocio]] - actores, valor y contexto institucional

## 07 - Metodologia
- [[reglas-de-oro]] - reglas para no romper nada
- [[convencion-de-ramas]] - convencion de ramas
- [[convencion-de-commits]] - formato de mensajes
- [[tablero-trabajo-en-curso]] - reserva tu tarea antes de tocar codigo
- [[nombres-de-rama-sugeridos]] - ramas propuestas por funcionalidad

## 99 - Plantillas
- [[plantilla-historia-de-usuario]] · [[plantilla-caso-de-uso]] · [[plantilla-caso-de-prueba]] · [[plantilla-adr]] · [[plantilla-nota-de-modulo]]
