---
tipo: plan
descripcion: Plan de inicio a fin para completar la monetización con banners (ADR-0030) — con enseñanzas
actualizado: 2026-07-19
---

# Plan · Banner de anuncios hasta su funcionamiento total

Continúa lo implementado en [[adr-0030-banner-de-anuncios-en-registro-publico]]. Cada paso
está marcado con quién lo hace: **🤖 IA** (Claude u otra IA ejecutora) o **👤 Kevin**.

> **Para la IA ejecutora:** antes de tocar código lee `CLAUDE.md` (raíz y por carpeta),
> [[reglas-de-oro]], [[convencion-de-commits]] y [[convencion-de-ramas]]. Commits en español,
> imperativo, `feat:`/`fix:`/`docs:`…, un cambio coherente por commit, referenciando el ADR
> (ej. `feat: reporte por patrocinador (ADR-0030)`). Ramas `feat/*` desde `develop`. Nada de
> cambios solo de formato. Tests con `--filter`; la suite completa la corre Kevin.

## Dónde estamos (ya implementado, 2026-07-19)

```
Participante                         Servidor (Laravel)                  Superadmin
────────────                         ──────────────────                  ──────────
escanea QR ──► /events/acceso/{slug} ──► elige banner vigente al azar    /administracion/banners
                    │                        │ +1 impresión                  │ CRUD + imagen
                    ▼                        ▼                               ▼
              ve el banner ◄──── imagen desde la BD (banner_files)      contadores
                    │                                                   impresiones/clics
                    └─ clic ──► /banners/{id}/ir ──► +1 clic ──► web del patrocinador
```

- Modelos `Banner`/`BannerFile`, migración, CRUD superadmin, parcial público descartable,
  contadores, rutas con `throttle:public`, 6 tests en `tests/Feature/Configuration/BannerTest.php`.

## 📚 Enseñanza 1 — Las tres métricas que pagan

```
  Impresiones  ──►  Clics  ──►  CTR (click-through rate)
  "lo vieron"      "les          CTR = clics / impresiones × 100
                   interesó"     Ej: 1.000 impresiones, 12 clics → CTR 1,2 %
```

Un patrocinador paga por **exposición** (impresiones) o por **interés** (clics). El CTR dice
si el banner funciona: bajo 0,5 % el diseño del banner no está llamando la atención. Sin estas
tres cifras no puedes ni fijar precio ni demostrar que se cumplió lo prometido.

## Fase 1 — Ordenar la casa (rama y commit)

1. **👤 Kevin:** decidir si los banners van en rama propia (`feat/banner-anuncios-registro`
   desde `develop`, recomendado) o dentro de la rama actual.
2. **🤖 IA / 👤:** commit según [[convencion-de-commits]]:
   `feat: ADR-30 banner de anuncios en el registro público por QR`.

## Fase 2 — Métricas honestas (impresión solo si se vio)

Hoy la impresión se cuenta **en el servidor al renderizar la página**, aunque el visitante
hubiera descartado el banner en esa sesión. Para facturar con evidencia:

1. **🤖 IA:** dejar de contar en `EventController::access`; crear ruta pública
   `POST /banners/{banner}/impresion` (con `throttle:public`).
2. **🤖 IA:** en el parcial `partials/ad-banner.blade.php`, enviar la impresión con
   `navigator.sendBeacon()` **solo cuando el banner realmente se mostró** (no estaba
   descartado por `sessionStorage`).
3. **🤖 IA:** actualizar `BannerTest` (la impresión ya no sube con el GET de la página).
4. Commit: `fix: cuenta impresiones solo cuando el banner se muestra (ADR-0030)`.

### 📚 Enseñanza 2 — ¿Por qué `sendBeacon` y no `fetch` normal?

```
 Página cargando ──► banner visible ──► sendBeacon("…/impresion")
                                            │ (el navegador lo envía aparte,
                                            ▼  aunque el usuario cierre la pestaña)
                                        servidor: +1
```

`sendBeacon` es un envío "de una sola vía" pensado para telemetría: no bloquea la página, no
espera respuesta y sobrevive al cierre de la pestaña. Exactamente lo que necesita un contador.

## Fase 3 — Reporte por patrocinador (para poder cobrar)

1. **🤖 IA:** tabla `banner_stats_daily` (banner_id, fecha, impresiones, clics) **o** consulta
   sobre contadores + registro por día; decidir en un mini-ADR si el volumen lo amerita.
2. **🤖 IA:** en `/administracion/banners`, botón "Reporte" por banner: rango de fechas,
   totales, CTR y exportación a Excel (reutilizar el patrón `app/Exports/`).
3. **🤖 IA:** tests del reporte. Commit: `feat: reporte de impresiones y clics por banner (ADR-0030)`.

### 📚 Enseñanza 3 — Cómo se cobra esto en la práctica

```
 Modelo A: tarifa plana        Modelo B: CPM
 "$X por estar 1 mes"          "$X por cada 1.000 impresiones"
 └─ simple, ideal al inicio    └─ exige métricas confiables (Fase 2)
```

Con poco tráfico conviene el **modelo A** (el patrocinador paga presencia, no volumen), y el
reporte sirve como evidencia de cumplimiento, no como base de cobro.

## Fase 4 — Rotación ponderada (opcional)

1. **🤖 IA:** columna `weight` (entero, default 1) + selección aleatoria ponderada en el scope.
2. Commit: `feat: rotación ponderada de banners por peso (ADR-0030)`.

```
 weight 3 ──► ███ 3 de cada 6 visitas      El patrocinador que paga más,
 weight 2 ──► ██  2 de cada 6              aparece más. La suma de pesos
 weight 1 ──► █   1 de cada 6              es el "total de boletas de la rifa".
```

## Fase 5 — Despliegue a producción (Hostinger)

1. **👤 Kevin:** seguir [[guia-de-actualizacion-hostinger]] (subir código, `php artisan migrate`
   en producción — crea `banners` y `banner_files`).
2. **👤 Kevin:** crear un banner de prueba en `/administracion/banners` de producción y abrir
   un evento por QR desde el celular (red móvil, no wifi de la U) para verlo en vivo.
3. **🤖 IA (si algo falla):** diagnosticar con los logs; la imagen viene de la BD, así que no
   depende de carpetas del hosting (misma razón que [[adr-0017-pdf-de-formato-en-bd]]).

## Fase 6 — Lo no técnico (sin esto no hay dinero)

1. **👤 Kevin:** aclarar la **autorización institucional** para mostrar publicidad en una
   página del dominio de la universidad (pendiente marcado en el ADR). Enfocarlo como
   "patrocinio de eventos" facilita el sí.
2. **👤 Kevin:** armar el mini-kit del patrocinador: especificación de la pieza
   (horizontal ≈ 600×80 px, JPG/PNG/WebP ≤ 2 MB — ideal WebP ≤ 100 KB), tarifa y duración.
3. **👤 Kevin:** conseguir el primer patrocinador real y cargar su banner.

## Verificación de fin del plan (todo debe cumplirse)

- [ ] Impresiones se cuentan solo cuando el banner se muestra; clics redirigen y cuentan.
- [ ] Reporte por banner con rango de fechas, CTR y export a Excel.
- [ ] Banners en producción, visibles al escanear un QR real desde un celular.
- [ ] `php artisan test --filter=Banner` en verde; suite completa corrida por Kevin.
- [ ] Autorización institucional resuelta y primer banner real cargado.
- [ ] Vault actualizado: ADR-0030 con estado final, [[roadmap]] y este plan marcado como hecho.
