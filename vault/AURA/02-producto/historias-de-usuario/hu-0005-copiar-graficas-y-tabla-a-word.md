---
tipo: historia-usuario
descripcion: El administrativo copia las gráficas y su tabla de datos para pegarlas en Word/Excel
actualizado: 2026-06-25
---

# HU-0005 · Copiar gráficas y su tabla de datos para los informes

**Como** administrativo que redacta informes,
**quiero** **copiar las gráficas** y **la tabla de datos** que generan,
**para** pegarlas directamente en Word/Excel y armar el informe en minutos.

## Contexto / valor
El dashboard de estadísticas no es solo para mirar: cada gráfica se puede **llevar al informe**.
La gráfica se copia **como imagen** y, además, sus **datos** se copian como **tabla** lista para
pegar. Esto convierte el análisis en entregable sin rehacer nada a mano.

> Truco para Word: tras **Copiar tabla**, en Word pega y usa **Insertar → Tabla → Convertir texto
> en tabla** (los datos van separados por tabulaciones), y queda como una tabla con formato.

## Criterios de aceptación
- [x] Cada gráfica tiene **Copiar como imagen** (PNG al portapapeles; si el navegador no lo permite,
      se **descarga** la imagen).
- [x] Cada gráfica tiene **Ver datos**: abre la tabla de datos con totales y porcentajes.
- [x] Puedo **Copiar la tabla** (texto separado por tabulaciones) y pegarla en Word/Excel.
- [x] Puedo **descargar** los datos como **CSV** o **Excel** (`.xls`), incl. tablas multi-columna.
- [x] La imagen copiada respeta el **tema** (claro/oscuro) e incluye título y leyenda.

## Estado
🟢 **Implementada** — `resources/js/statistics/components/ChartToolbar.jsx`
(`captureAsImage`, `copyTableText`, `downloadCSV`, `downloadExcel`) y `ChartModal.jsx` (modal de
datos). Parte de las estadísticas en React ([[adr-0001-react-islands-estadisticas]]).

## Notas técnicas
- `copyTableText` usa separador **tabulador** justamente para que pegue bien como tabla en
  Word/Excel.
- La captura serializa el SVG + leyenda HTML a `<canvas>` y lo escribe como imagen al portapapeles.

## Pruebas relacionadas
Lógica de cliente (JS); sin test automatizado hoy. Ver [[estrategia-de-pruebas]].
