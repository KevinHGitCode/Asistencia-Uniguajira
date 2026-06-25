---
tipo: adr
descripcion: ADR-0014 (propuesta) — Proteger o retirar las rutas API de prueba sin autenticación
actualizado: 2026-06-21
---

# ADR-0014 · Proteger/retirar las rutas API de prueba sin autenticación

- **Estado:** 🟢 Implementado
- **Fecha:** 2026-06-21
- **Implementado:** 2026-06-21
- **Contexto del repo:** `routes/api.php`, bloque `RUTAS DE PRUEBA` al final del archivo.
  Relacionado con [[brechas-conocidas]] #4 y [[adr-0005-rate-limiting-anti-abuso]].

## Contexto
Al final de `routes/api.php` hay un bloque rotulado **`RUTAS DE PRUEBA`** que quedó del desarrollo
inicial y sigue **público, sin `auth` ni filtro de sede**:

El bloque resultó **mayor de lo inventariado**: eran **12** endpoints (no 5), todos `GET` públicos:

| Ruta | Qué devolvía |
|---|---|
| `GET /api/events` | **Todos** los eventos (`Event::all()`) |
| `GET /api/events/user/{user_id}` | Eventos de cualquier usuario por id |
| `GET /api/events-with-user` | Todos los eventos + datos del usuario creador |
| `GET /api/participants` | **Todos** los participantes (`Participant::all()`) — datos personales |
| `GET /api/participants/program/{program_id}` | Participantes de un programa |
| `GET /api/participants/count-by-program` | Conteo de participantes por programa |
| `GET /api/roles` | Estamentos (`ParticipantType`) |
| `GET /api/programs` | **Todos** los programas |
| `GET /api/affiliations` | **Todas** las afiliaciones |
| `GET /api/attendances` | **Todas** las asistencias |
| `GET /api/users` | **Todos** los usuarios (`User::all()`) — ⚠️ el más sensible |
| `GET /api/dependencies` | **Todas** las dependencias |

Cualquiera con la URL podía **cosechar (scraping)** toda la base —incluidos **usuarios** y datos
personales de participantes— sin iniciar sesión y sin respetar la separación por sede. Fuga de datos
e incumplimiento de la migración multi-sede ([[migracion-multi-sede]]).

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

## Implementación

- **Auditoría (paso 1):** búsqueda en `resources/js/**`, vistas y `tests/**`. **Ningún** front, vista
  ni test consume estas rutas. La única referencia parecida es `resources/js/calendar/paint.js`
  con `/api/events/${date}` → es `getByDate` (`['web','auth']`), **otra** ruta. Confirmadas huérfanas.
- **Acción:** se **eliminó** el bloque completo "RUTAS DE PRUEBA" (12 rutas) de `routes/api.php` y
  los imports que quedaron sin uso (`Participant`, `Affiliation`, `Program`, `Attendance`,
  `Dependency`). No hizo falta proteger ninguna (ninguna se usa).
- **Rama:** `fix/retirar-rutas-api-de-prueba`.
- **Tests:** `tests/Feature/Api/RemovedTestRoutesTest.php` — las 12 rutas devuelven 404 y se verifica
  que `/api/events/{date}` (legítima) sigue exigiendo auth. Suite de rutas/calendario en verde.
- Si en el futuro se necesita un endpoint de datos (p. ej. el `/api/participants` que plantea
  [[adr-0008-listado-participantes-en-react]]), se definirá **nuevo**, bajo `['web','auth']` y con
  `CampusScopeService`.

## Pendiente para aceptar
- [ ] Auditoría de uso (paso 1): ¿algún front/integración las invoca?
- [ ] Decidir por cada ruta: eliminar vs. proteger.
- [ ] Rama sugerida: `fix/retirar-rutas-api-de-prueba` (🔴 cambia contratos de `api.php`).

## Relacionado
[[brechas-conocidas]] · [[adr-0005-rate-limiting-anti-abuso]] · [[migracion-multi-sede]] · [[arquitectura]]
