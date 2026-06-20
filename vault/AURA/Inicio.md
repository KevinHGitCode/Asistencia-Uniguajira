---
tipo: inicio
descripcion: Mapa de contenido (MOC) del vault AURA — puerta de entrada a todo el conocimiento
actualizado: 2026-06-20
---

# 🏠 Inicio — AURA

Banco de conocimiento de **Asistencia Uniguajira**. Empieza aquí. Cada enlace lleva a una
nota; cada nota es una idea. Lee primero [[README]] si es tu primera vez.

> **Atajo mental:** ¿qué *es* hoy el sistema? → 01. ¿Qué *queremos*? → 02. ¿Cómo está
> *construido*? → 03. ¿Cómo *aseguramos* que funciona? → 04. ¿Cómo *trabajamos juntos*? → 07.

## 01 · Estado actual (fiel al código)
- [[stack-tecnologico]] — versiones reales de backend, frontend y datos
- [[modelo-de-datos]] — tablas, relaciones y las piezas clave (`participant_roles`, `attendance_details`)
- [[mapa-de-modulos]] — qué módulos existen y su grado de implementación
- [[brechas-conocidas]] — lo que la UI/los docs prometen vs. lo que el backend entrega

## 02 · Producto
- [[vision]] — para qué existe el sistema
- [[personas-y-roles]] — quién lo usa (admin, usuario de dependencia, participante)
- [[roadmap]] — hacia dónde va
- [[modulos]] — índice de módulos del producto
- Historias de usuario → [[hu-0001-registro-asistencia-qr]]
- Casos de uso → [[cu-0001-descargar-pdf-asistencia]]

## 03 · Diseño
- [[arquitectura]] — cómo encajan las piezas (Livewire + React islands + servicios)
- [[convenciones]] — resumen de las reglas de código (las vinculantes viven en `CLAUDE.md`)
- Decisiones (ADR):
  - [[adr-0001-react-islands-estadisticas]] 🟢
  - [[adr-0002-snapshot-demografico-attendance-details]] 🟢
  - [[adr-0003-retirar-flujo-legacy-de-asistencia]] 🟡

## 04 · Calidad
- [[estrategia-de-pruebas]] — qué probamos y por qué
- [[convenciones-de-pruebas]] — cómo escribimos los tests

## 05 · Ideas
- [[ideas]] — backlog de ideas y exploraciones

## 06 · Negocio
- [[contexto-negocio]] — actores, valor y contexto institucional

## 07 · Metodología (multi-IA / multi-persona)
- [[reglas-de-oro]] — las 10 reglas para no romper nada
- [[convencion-de-ramas]] — `feat/ fix/ refactor/ docs/ test/` en kebab-case
- [[convencion-de-commits]] — formato de mensajes
- [[tablero-trabajo-en-curso]] — reserva tu tarea aquí antes de tocar código
- [[nombres-de-rama-sugeridos]] — rama propuesta por funcionalidad (🟢 paralela / 🔴 serializa)

## 99 · Plantillas
- [[plantilla-historia-de-usuario]] · [[plantilla-caso-de-uso]] · [[plantilla-caso-de-prueba]] · [[plantilla-adr]] · [[plantilla-nota-de-modulo]]
