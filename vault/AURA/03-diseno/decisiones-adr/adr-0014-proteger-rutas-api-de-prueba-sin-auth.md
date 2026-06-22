---
tipo: adr
descripcion: ADR-0014 (propuesta) — Proteger o retirar las rutas API de prueba sin autenticación
actualizado: 2026-06-21
---

# ADR-0014 · Proteger/retirar las rutas API de prueba sin autenticación

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-21
- **Contexto del repo:** `routes/api.php`, bloque `RUTAS DE PRUEBA` al final del archivo.
  Relacionado con [[brechas-conocidas]] #4 y [[adr-0005-rate-limiting-anti-abuso]].

## Contexto
Al final de `routes/api.php` hay un bloque rotulado **`RUTAS DE PRUEBA`** que quedó del desarrollo
inicial y sigue **público, sin `auth` ni filtro de sede**:

| Ruta | Qué devuelve |
|---|---|
| `GET /api/events` | **Todos** los eventos (`Event::all()`) |
| `GET /api/events/user/{user_id}` | Eventos de cualquier usuario por id |
| `GET /api/events-with-user` | Todos los eventos + datos del usuario creador |
| `GET /api/participants` | **Todos** los participantes (`Participant::all()`) — incluye datos personales |
| `GET /api/participants/program/{program_id}` | Participantes de un programa |

Cualquiera con la URL puede **cosechar (scraping)** toda la base de eventos y participantes —
incluidos datos personales de participantes— sin iniciar sesión y sin respetar la separación por
sede. Es una fuga de datos y un incumplimiento de la migración multi-sede ([[migracion-multi-sede]]).

## Decisión
**Retirar** las rutas de prueba que ya no se usan y **proteger** las que sí se necesiten:

1. **Auditar el uso real**: confirmar (búsqueda en `resources/js/**` y vistas) si algún front
   todavía llama a estos endpoints. Hoy el calendario usa `/api/eventos-json` y `/api/events/{date}`,
   no estas — así que se presumen huérfanas.
2. **Eliminar** las rutas huérfanas (`/api/events`, `/api/events/user/{id}`, `/api/events-with-user`,
   `/api/participants`, `/api/participants/program/{id}`).
3. Si alguna debe sobrevivir, **moverla detrás de** `['web', 'auth']` (+ `role:` si aplica) y
   aplicarle `CampusScopeService` para no mezclar sedes, igual que el resto de `/api/*`.
4. Dejar el archivo `routes/api.php` sin el rótulo "RUTAS DE PRUEBA".

## Consecuencias
- ➕ Cierra una fuga de datos (eventos + datos personales de participantes) accesible sin login.
- ➕ Alinea estas rutas con la separación por sede.
- ➖ Si algún script/integración no documentada las usa, dejará de funcionar → mitigar con la
  auditoría del paso 1 antes de borrar.
- 🔁 Cambia el contrato de `routes/api.php` (rutas dejan de existir o ahora exigen sesión → 401/302).

## Alternativas consideradas
- **Solo rate limiting** (ADR-0005): reduce el scraping masivo pero **no** impide que un anónimo lea
  los datos; el problema de fondo es la falta de `auth`. Complementario, no sustituto.
- **Dejarlas como están**: mantiene la fuga de datos. Descartada.

## Pendiente para aceptar
- [ ] Auditoría de uso (paso 1): ¿algún front/integración las invoca?
- [ ] Decidir por cada ruta: eliminar vs. proteger.
- [ ] Rama sugerida: `fix/retirar-rutas-api-de-prueba` (🔴 cambia contratos de `api.php`).

## Relacionado
[[brechas-conocidas]] · [[adr-0005-rate-limiting-anti-abuso]] · [[migracion-multi-sede]] · [[arquitectura]]
