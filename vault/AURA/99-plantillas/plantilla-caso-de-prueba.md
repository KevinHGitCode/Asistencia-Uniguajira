---
tipo: plantilla
descripcion: Plantilla para documentar un caso de prueba
actualizado: 2026-06-20
---

# Plantilla · Caso de prueba

> Para documentar pruebas relevantes (manuales o automatizadas). Convenciones en
> [[convenciones-de-pruebas]]; cobertura en [[estrategia-de-pruebas]].

```markdown
---
tipo: caso-prueba
descripcion: <una línea>
actualizado: AAAA-MM-DD
---

# CP-XXXX · <título>

- **Tipo:** Feature | Unit | Manual
- **Cubre:** <HU/CU/módulo, p.ej. [[hu-0001-registro-asistencia-qr]]>
- **Archivo:** `tests/Feature/...Test.php` (si automatizado)

## Precondiciones / datos
<seed, escenario, usuario y rol>

## Pasos
1. <acción>
2. <acción>

## Resultado esperado
- <aserción concreta>

## Variantes / bordes
- <permiso denegado, duplicado, datos vacíos, concurrencia…>
```
