---
tipo: plan
descripcion: Plan de inicio a fin para que "asistencia uniguajira" y "aura uniguajira" muestren la app de primera en Google (Colombia) — con enseñanzas
actualizado: 2026-07-19
---

# Plan · SEO — aparecer de primero para "asistencia uniguajira" / "aura uniguajira"

**Meta:** que al buscar en Google **"asistencia uniguajira"** o **"aura uniguajira"**, sobre
todo desde Colombia, la aplicación sea el **primer resultado**. Cada paso marcado:
**🤖 IA** o **👤 Kevin**.

> **Para la IA ejecutora:** leer `CLAUDE.md` (raíz y por carpeta), [[reglas-de-oro]],
> [[convencion-de-commits]] y [[convencion-de-ramas]]. Rama sugerida: `feat/seo-landing-publica`
> desde `develop`. Commits en español, imperativo, un cambio por commit. Las páginas públicas
> deben seguir **ligeras** (sin React — regla de `resources/js/CLAUDE.md`).

## 📚 Enseñanza 1 — Cómo te encuentra Google (el ciclo completo)

```
 1. RASTREO             2. INDEXACIÓN              3. RANKING
 Googlebot visita  ──►  Google guarda la página ──► ante una búsqueda, ordena
 tus URLs               en su índice (su            los resultados por
 (si puede entrar)      "biblioteca")               relevancia + autoridad
```

Si el robot **no puede entrar** (páginas con login) o le **prohibimos** pasar (robots.txt),
la página no existe para Google. Nuestro problema nº 1: **casi toda la app está tras login**
— `/` redirige a `/dashboard` y las únicas páginas públicas son las de acceso por QR, que
además **no queremos** que salgan en Google (son de eventos concretos).

## 📚 Enseñanza 2 — Por qué esta meta es alcanzable

```
 "comprar zapatos"            → millones compiten  → muy difícil
 "asistencia uniguajira"      → búsqueda DE MARCA  → compiten ~2-3 sitios
 "aura uniguajira"            → marca aún más única → casi sin competencia
```

Las búsquedas **de marca** tienen poquísima competencia: básicamente el sitio oficial
`uniguajira.edu.co` y redes sociales. Con lo técnico bien hecho + una señal de autoridad
(Fase 5), el primer puesto es realista en semanas, no años.

## Fase 0 — Requisito previo

1. **👤 Kevin:** confirmar el **dominio definitivo** de producción y anotarlo aquí:
   `__________` (¿subdominio de `uniguajira.edu.co`? ¿dominio propio?). Todo el plan lo usa.
   - Si se puede lograr un subdominio institucional (ej. `aura.uniguajira.edu.co`), es la
     ventaja más grande de todo el plan: hereda la autoridad del dominio de la universidad.

## Fase 1 — Una puerta de entrada pública (la landing)

Hoy no hay **nada** que Google pueda indexar. Hay que darle una página.

1. **🤖 IA:** crear `GET /` público (vista Blade `landing.blade.php`): qué es AURA /
   Asistencia Uniguajira, para quién es, capturas, y botón "Iniciar sesión". Los usuarios
   autenticados siguen redirigidos a `/dashboard`.
2. **🤖 IA:** contenido en español con las palabras clave **naturales** en título y
   encabezados: "AURA — Sistema de Asistencia de la Universidad de La Guajira".
3. Commit: `feat: landing pública indexable para SEO`.

### 📚 Enseñanza 3 — La anatomía de un resultado de Google

```
 ┌─────────────────────────────────────────────────┐
 │ AURA — Asistencia Uniguajira          ← <title>  │
 │ https://el-dominio.edu.co             ← URL      │
 │ Sistema de control de asistencia de   ← <meta    │
 │ eventos de la Universidad de La         name=    │
 │ Guajira. Registro por código QR…        "description"> │
 └─────────────────────────────────────────────────┘
```

El `<title>` es el factor on-page más importante y **es** el titular azul del resultado.
La `description` no afecta el ranking, pero decide si la gente hace clic.

## Fase 2 — Higiene técnica (decirle a Google qué sí y qué no)

1. **🤖 IA:** `<title>` y `<meta name="description">` únicos en la landing; etiquetas
   Open Graph (`og:title`, `og:image`…) para que al compartir por WhatsApp se vea bien
   (en Colombia, WhatsApp es la mitad de la difusión real).
2. **🤖 IA:** `public/robots.txt`: permitir `/`, bloquear `/dashboard`, `/administracion`,
   `/estadisticas`, `/usuarios`, `/settings`.
3. **🤖 IA:** `<meta name="robots" content="noindex">` en `/events/acceso/{slug}`: las
   páginas de eventos no deben competir con la landing ni exponer eventos en Google.
4. **🤖 IA:** `sitemap.xml` (con la landing; crecerá si hay más páginas públicas) y
   `rel="canonical"` en la landing.
5. Commit: `feat: robots, sitemap, metadatos y noindex de páginas QR`.

### 📚 Enseñanza 4 — robots.txt vs noindex (se confunden siempre)

```
 robots.txt  = "no ENTRES aquí"        (el robot no visita la página)
 noindex     = "entra, pero no la MUESTRES en resultados"
 ⚠ Si bloqueas con robots.txt, Google NO puede leer el noindex.
```

Por eso las páginas QR llevan `noindex` (queremos que Google lo lea) y las zonas privadas
van en robots.txt (ni siquiera necesita intentarlo: el login lo pararía igual).

## Fase 3 — Datos estructurados (JSON-LD)

1. **🤖 IA:** bloque `<script type="application/ld+json">` en la landing con el esquema
   `Organization` + `WebApplication`: nombre ("AURA", "Asistencia Uniguajira"), URL, logo,
   y relación con la Universidad de La Guajira (`parentOrganization`).
2. Commit: `feat: datos estructurados JSON-LD en la landing`.

> 📚 **Enseñanza 5:** los datos estructurados no suben el ranking directamente; le dicen a
> Google **qué eres** sin ambigüedad ("AURA" también es una palabra común — así asociamos
> el nombre a la universidad) y habilitan resultados enriquecidos (logo, sitelinks).

## Fase 4 — Registrar el sitio ante Google (esto es tuyo)

1. **👤 Kevin:** crear cuenta en **Google Search Console** (search.google.com/search-console),
   añadir el dominio y verificar propiedad (etiqueta HTML que la 🤖 IA puede dejar puesta,
   o registro DNS si el dominio es tuyo).
2. **👤 Kevin:** enviar el `sitemap.xml` y usar "Inspección de URLs → Solicitar indexación"
   para la landing.
3. **👤 Kevin:** en adelante, Search Console es el tablero: qué búsquedas te muestran, en qué
   posición y cuántos clics recibes.

```
 Día 0: solicitas indexación ──► Días 1-7: Google indexa ──► Semanas 2-6:
 el ranking se acomoda (primero apareces, luego subes posiciones)
```

## Fase 5 — Autoridad: el empujón que decide el primer puesto

1. **👤 Kevin:** conseguir **un enlace desde `uniguajira.edu.co`** hacia la app (página de
   bienestar, noticia institucional, directorio de sistemas). Es la señal de autoridad más
   fuerte disponible y probablemente decide la meta por sí sola.
2. **👤 Kevin:** enlaces secundarios: redes oficiales de la universidad/semillero SIIS2,
   perfil de Google (si aplica), repositorios o notas de prensa.

### 📚 Enseñanza 6 — Por qué los enlaces mandan

```
            uniguajira.edu.co (autoridad alta, .edu.co)
                   │  enlace = "yo respondo por este sitio"
                   ▼
              tu aplicación  ──► Google: "si la universidad lo enlaza,
                                  es el resultado legítimo para su marca"
```

Google nació de esta idea (PageRank): cada enlace es un voto, y los votos de sitios con
autoridad valen más. Un solo enlace institucional supera meses de ajustes técnicos.

## Fase 6 — Rendimiento y señal "Colombia"

1. **🤖 IA:** mantener la landing ligera (sin React, imágenes WebP, `loading="lazy"`) y
   verificarla con PageSpeed Insights (>90 en móvil como criterio).
2. **🤖 IA:** `<html lang="es">` correcto (ya aplica) y contenido con contexto local
   (La Guajira, Riohacha, Maicao — las sedes ya existen en el sistema).
3. **👤 Kevin:** nada que configurar por país: Google ya geolocaliza al buscador; un dominio
   `.edu.co` o `.co` refuerza la señal Colombia por sí mismo.

## Verificación de fin del plan (criterio de éxito medible)

- [ ] Landing pública indexada (Search Console: URL "indexada").
- [ ] `site:el-dominio` en Google muestra la landing y **no** muestra páginas de eventos QR.
- [ ] Buscar **"aura uniguajira"** desde Colombia → primer resultado (revisar en ventana de
      incógnito; en Search Console, posición media ≤ 1,5 para esa consulta).
- [ ] Buscar **"asistencia uniguajira"** → primer o segundo resultado (compite con el sitio
      oficial; si el enlace de la Fase 5 existe, primero es alcanzable).
- [ ] PageSpeed móvil de la landing > 90.
- [ ] Revisión mensual en Search Console anotada en [[tablero-trabajo-en-curso]].
