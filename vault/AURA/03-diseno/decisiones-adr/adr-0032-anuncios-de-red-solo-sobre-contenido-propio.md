---
tipo: adr
descripcion: ADR-0032 — Los anuncios de red (AdSense) no van en el registro por QR; requieren una landing con contenido propio
actualizado: 2026-07-20
---

# ADR-0032 · Anuncios de red solo sobre contenido propio

- **Estado:** 🟢 Aceptada (2026-07-20) — pendiente de implementación
- **Fecha:** 2026-07-20
- **Contexto del repo:** amplía [[adr-0030-banner-de-anuncios-en-registro-publico]], que dejó
  la red de anuncios como "diferida". Se relaciona con [[plan-seo-posicionamiento]].

## Contexto

Tras dejar funcionando los banners propios se planteó mostrar **anuncios de Google (AdSense)**
en la página pública de registro por QR (`events/access.blade.php`), para no depender de
conseguir patrocinadores.

Al revisar los requisitos aparecieron dos bloqueos, uno de trámite y otro de política:

1. AdSense exige **cuenta aprobada** y verificación del dominio (producción corre en
   `desarrollougmaicao.com`). Es un trámite humano, no técnico.
2. **La política "Valuable Inventory / No content" de AdSense prohíbe servir anuncios en
   pantallas sin contenido propio**: páginas de utilidad, formularios, confirmaciones, logins.
   La página del QR es exactamente eso — un formulario y nada más. Colocar anuncios ahí
   arriesga el rechazo de la solicitud y, si se aprobara, la **suspensión de la cuenta**.

Además, hoy el sitio es una aplicación tras login con esa única página pública: una solicitud
en ese estado probablemente ni pasaría la revisión inicial (mismo diagnóstico que la Fase 1 de
[[plan-seo-posicionamiento]]).

## Decisión

**Los anuncios de red no se colocan en la página de registro por QR.** El inventario válido
para AdSense será la **landing pública con contenido propio** que ya contempla el plan de SEO;
los anuncios irán únicamente allí.

Reparto de superficies:

| Superficie | Qué muestra |
|---|---|
| Registro por QR (`/events/acceso/{slug}`) | **Solo banners propios** (ADR-0030). Sin scripts de terceros. |
| Landing pública (futura) | Espacio para AdSense, cuando la cuenta esté aprobada. |

Orden acordado, y no se salta ningún escalón:

```
0. Prueba del consentimiento + política en dominio propio  → ADR-0033
1. Autorización institucional para publicidad              → 👤 pendiente de ADR-0030
2. Definir el diseño de la landing                         → 👤 Kevin
3. Construir landing + página de privacidad                → 🤖
4. SEO (robots, sitemap, metadatos, Search Console)        → plan-seo-posicionamiento
5. Solicitar AdSense y colocar anuncios solo en la landing
```

Cuando llegue el paso 5, los anuncios serán **no personalizados** por defecto, para reducir la
carga de consentimiento de cookies (ver [[adr-0033-prueba-del-consentimiento-de-datos]]).

## Consecuencias

- ➕ Se evita el riesgo de suspensión de la cuenta de AdSense por inventario sin contenido.
- ➕ La página del QR sigue **ligera y sin terceros**, coherente con su función y con la
  privacidad de quien escribe ahí su nombre y documento.
- ➕ Los banners propios dejan de ser "el plan B": son la fuente de ingreso de la superficie de
  mayor tráfico, y para una audiencia local cautiva suelen pagar mejor que AdSense.
- ➖ El ingreso por red de anuncios queda condicionado a construir la landing primero.
- 🔁 Si algún día la landing gana tráfico propio, el mismo hueco sirve para AdSense o para
  patrocinios directos, sin tocar la página del QR.

## Alternativas consideradas

- **Poner AdSense igual en la página del QR** — descartada: viola la política citada; el riesgo
  (perder la cuenta) supera con creces el ingreso esperable con este tráfico.
- **Otra red de anuncios con requisitos más laxos** — no explorada; las redes serias tienen
  políticas equivalentes sobre inventario sin contenido, y las que no, suelen degradar la
  experiencia y la reputación del dominio institucional.
