---
tipo: producto
descripcion: Personas/actores del sistema y sus roles técnicos
actualizado: 2026-06-20
---

# Personas y roles

Tres actores principales. Los permisos técnicos están en [[mapa-de-modulos]].

## 1. Administrador (`role = admin`)
- Quién: equipo del semillero **SIIS2** / responsables del sistema.
- Puede: todo. Gestión de usuarios (`usuarios/*`), administración del sistema
  (`administracion/*`: dependencias, áreas, programas, formatos, organizaciones, importación,
  auditoría) y estadísticas globales incl. `estadisticas/usuarios`.
- Seed: 5 admins creados por `DatabaseSeeder` (ver [[brechas-conocidas]] sobre contraseña demo).

## 2. Usuario de dependencia (rol normal)
- Quién: funcionario/gestor de una o varias **dependencias** (relación `belongsToMany`).
- Puede: crear y gestionar **sus** eventos y ver los de **sus dependencias**; descargar el
  PDF de asistencia; ver estadísticas de **sus** eventos (los endpoints summary filtran por rol).
- No puede: administración del sistema ni gestión de usuarios.

## 3. Participante / asistente (sin cuenta)
- Quién: estudiante, docente o **comunidad externa** que asiste a un evento.
- Puede: registrar su asistencia desde el **QR público** sin iniciar sesión.
- Flujo: busca por documento/código; si no existe y es externo, se da de alta con su
  organización; acepta tratamiento de datos; completa detalle demográfico. Ver
  [[registro-de-asistencia]] y [[hu-0001-registro-asistencia-qr]].
- Su identidad puede tener **varios roles** (estamentos) — ver [[modelo-de-datos]].

## Notas
- No existe un rol intermedio tipo "supervisor de dependencia" más allá de `admin` vs.
  normal; la segmentación real es por **pertenencia a dependencias** + dueño del evento.
