---
tipo: negocio
descripcion: Contexto institucional, actores y valor del sistema para la universidad
actualizado: 2026-06-20
---

# Contexto de negocio

## Organización
- **Universidad de La Guajira (Uniguajira)** — institución pública en Riohacha, La Guajira,
  Colombia.
- Desarrollado por el **semillero SIIS2** (aparece como "Soporte SIIS2" en la página pública).
- Marca pública del sistema: **AURA** (logo en el registro de asistencia). Ver nota sobre el
  solapamiento de nombre en [[brechas-conocidas]] #6.

## Para qué le sirve a la universidad
- **Evidencia formal** de asistencia a eventos (PDF con formato oficial por dependencia),
  útil para procesos administrativos, de calidad y **acreditación**.
- **Datos demográficos** de participación (programa, estamento, género, grupos priorizados,
  comunidad externa) para informes institucionales y de impacto social.
- **Eficiencia**: reemplaza planillas en papel por registro digital vía QR.

## Actores institucionales
- **Dependencias** (p.ej. Bienestar Universitario, Gestión Documental): organizan eventos y
  necesitan sus reportes con su formato.
- **Comunidad externa**: asistentes que no son estudiantes/docentes; se modelan con
  `Organization` (ver [[modelo-de-datos]]).
- **Grupos priorizados**: indígena, afrodescendiente, víctima del conflicto, LGTBQ+,
  habitante de frontera, discapacidad — relevantes en el contexto de La Guajira y para los
  informes de inclusión.

## Restricciones / sensibilidades
- **Tratamiento de datos personales**: el registro exige aceptación explícita.
- Producción en **Render** (recursos limitados) → atención al rendimiento (hay tests de
  rendimiento, ver [[estrategia-de-pruebas]]).

## Relacionado
[[vision]] · [[personas-y-roles]]
