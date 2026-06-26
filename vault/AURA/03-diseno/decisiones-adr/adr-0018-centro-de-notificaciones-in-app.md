---
tipo: adr
descripcion: ADR-0018 (propuesta) — Centro de notificaciones in-app (campana) para acciones asíncronas y avisos de eventos
actualizado: 2026-06-25
---

# ADR-0018 · Centro de notificaciones in-app (campana)

- **Estado:** 🟢 Aceptada — implementado (2026-06-25, rama `feat/importacion-participantes-async`)
- **Fecha:** 2026-06-25
- **Contexto del repo:** `app/Models/User.php` (ya usa el trait `Notifiable`),
  `resources/views/components/layouts/app/sidebar.blade.php` (menú de usuario al pie del sidebar en
  escritorio; `flux:header` solo en móvil), `resources/views/components/layouts/app/header.blade.php`,
  `app/Models/Event.php` (`start_date` / `end_date`, `user_id`, `dependency_id`),
  `app/Http/Controllers/Configuration/ParticipantImportController.php` (lotes de importación,
  ADR-0004), `QUEUE_CONNECTION=database`. Despliegue en Hostinger (hosting compartido, sin demonios
  persistentes). Modelo de datos en [[modelo-de-datos]].

## Contexto
Varias acciones del sistema ocurren (o van a ocurrir) **fuera del request del usuario** y hoy no
tienen forma de avisarle:

- **Importación de participantes** (ADR-0004): el parseo asíncrono terminará en segundo plano; el
  usuario necesita enterarse de que **su lote ya está listo para revisar** sin quedarse mirando la
  pantalla.
- **Eventos próximos**: a un responsable le sirve un aviso de que **su evento empieza pronto**.
- **Eventos por finalizar**: un aviso de que **el evento está por terminar** (cierre de registro,
  descarga del PDF, etc.).

Hoy el único feedback es el **flash de sesión** (`with('success', …)`), que es efímero: se pierde al
navegar y solo existe en el request que lo generó. No hay un lugar persistente donde el usuario vea
"qué pasó mientras no estaba mirando".

El roadmap ya listaba **"Notificaciones reales"** como candidato ([[roadmap]]); este ADR lo promueve
a decisión.

## Decisión propuesta
Introducir un **centro de notificaciones in-app**: un ícono de **campana** arriba a la derecha con
un contador de no leídas y un desplegable con las notificaciones recientes, respaldado por las
**notificaciones de base de datos nativas de Laravel**.

### 1. Persistencia — `notifications` (canal `database` de Laravel)
- `User` ya usa `Notifiable`. Se añade la tabla estándar `notifications`
  (`php artisan make:notifications-table`): `id` (uuid), `type`, `notifiable_type/id` (morph al
  usuario), `data` (JSON), `read_at`, timestamps. **No** inventamos esquema propio.
- Cada notificación se define como una clase en `app/Notifications/` que implementa
  `toArray()` con: `tipo` (p. ej. `import.listo`, `evento.proximo`, `evento.por-finalizar`),
  `titulo`, `mensaje`, `url` (a dónde lleva al hacer clic) e `icono`.
- 🔴 Migración nueva.

### 2. Productores de notificaciones
| Origen | Disparador | Destinatario | URL al hacer clic |
|---|---|---|---|
| **Importación (ADR-0004)** | El job de parseo termina y el lote pasa a `en_revision` | El usuario que subió el lote | `participants-import.review` |
| **Evento próximo** | Falta ≤ X tiempo para `start_date` | `Event->user` (responsable) | detalle del evento |
| **Evento por finalizar** | Falta ≤ Y tiempo para `end_date` | `Event->user` (responsable) | detalle del evento |

El destinatario sale del propio dato (`import_batch.user_id`, `event.user_id`), así que el
**scope por sede es automático**: cada quien solo recibe lo suyo. El escaneo de eventos respeta
[[migracion-multi-sede]] al elegir destinatarios.

### 3. Cómo se generan las notificaciones de tiempo (eventos)
Un **comando programado** (`php artisan` + `schedule()` en `routes/console.php`) escanea los eventos
y crea las notificaciones. Se apoya en el **mismo cron de Hostinger** que necesita ADR-0004:

```
* * * * * cd /ruta && php artisan schedule:run >> /dev/null 2>&1
```

`schedule:run` dispara, en cada tick:
- `queue:work --stop-when-empty` (procesa el parseo encolado de ADR-0004), y
- `notifications:escanear-eventos` (crea avisos de "próximo" / "por finalizar").

**Idempotencia (clave):** el escaneo corre cada minuto, así que **no debe** reenviar el mismo aviso
en cada tick. Se marca el evento como ya notificado con columnas ligeras
(`reminder_notified_at`, `ending_notified_at`) o, alternativamente, comprobando si ya existe una
notificación de ese `tipo` + evento. → **Decisión a confirmar** (ver "Pendiente para aceptar").

### 4. Entrega a la UI — campana + poll (sin websockets)
- Componente **Livewire `NotificationBell`** montado en el layout: ícono `flux:icon.bell` con
  **badge** del conteo de no leídas.
- **`wire:poll.30s`** (o 60s) refresca conteo y lista. Es la opción correcta para hosting
  compartido: **sin servidor de websockets**, una query `COUNT` barata por tick.
- Además, eventos Livewire (`Livewire.dispatch('notificacion-nueva')`) para refrescar al instante
  cuando la acción ocurre en la misma sesión.
- **Ubicación "arriba a la derecha":** hoy el escritorio usa **sidebar** (menú de usuario al pie,
  sin header superior). Para cumplir "arriba a la derecha" se añade una franja/área superior
  derecha fija en escritorio que aloje la campana; en **móvil** entra directo en el `flux:header`
  existente, junto al perfil. → **Decisión de ubicación a confirmar** (franja superior nueva vs.
  campana fija flotante vs. dentro del sidebar).

### 5. UX del desplegable
- Lista de las ~10 más recientes; no leídas resaltadas.
- Clic en una → la marca como leída y **navega** a su `url` (`wire:navigate`).
- Acción **"Marcar todas como leídas"**.
- Estado vacío amable ("Sin notificaciones por ahora").
- (Futuro, opcional) página `/notificaciones` con historial paginado completo.

### 6. Retención / limpieza
La tabla `notifications` crece sin techo. Política: un comando programado **borra las leídas con más
de N días** (p. ej. 30) y, opcionalmente, las no leídas muy viejas. Se alinea con la política de
retención de lotes de ADR-0004 (misma sección de housekeeping del cron). → **Definir N al aceptar.**

## Consecuencias
- ➕ **Feedback asíncrono unificado:** la importación deja de exigir que el usuario "se quede
  mirando"; se le avisa esté donde esté en la app. Cierra el bucle de UX del parseo encolado
  (ADR-0004).
- ➕ Reutiliza la infraestructura **probada** de notificaciones de Laravel (canal `database`); poco
  código propio.
- ➕ Extensible: agregar un nuevo aviso = una clase `Notification` + dónde se dispara.
- ➕ Multi-canal a futuro sin reescribir (se puede añadir `mail` al mismo `Notification`).
- ➖ **Esquema nuevo** (`notifications` + posibles columnas `*_notified_at` en `events`) → 🔴.
- ➖ Requiere que el **cron de Hostinger ejecute `schedule:run`** (mismo requisito que ADR-0004).
- ➖ El **poll** añade carga ligera (un `COUNT` por usuario activo cada 30–60s) y los avisos de
  tiempo tienen la **latencia del cron** (hasta ~1 min), aceptable para recordatorios.

## Alternativas consideradas
- **Solo toasts (flash):** lo que hay hoy. Efímero, se pierde al navegar y no sirve para procesos
  asíncronos. Es justo lo que queremos superar.
- **WebSockets / Laravel Echo (Reverb/Pusher):** entrega en tiempo real, pero exige un **servidor
  persistente** o un servicio pago; **inviable en Hostinger compartido**. El poll cubre el caso de
  uso (recordatorios y "lote listo" no necesitan milisegundos).
- **Solo correo electrónico:** útil como canal *adicional*, pero no es "in-app" ni da el ícono de
  campana que se pidió. Queda como posible segundo canal del mismo `Notification`.
- **Tabla de notificaciones propia:** reinventa lo que `Notifiable` + canal `database` ya dan.

## Implementación (2026-06-25)

**Hecho:**
- [x] Tabla `notifications` estándar (canal `database`; migración `2026_06_25_000002`).
- [x] Notificaciones `App\Notifications\{ImportBatchReady, EventStartingSoon, EventEndingSoon}`
  (`toArray`: `tipo`, `titulo`, `mensaje`, `url`, `icono`).
- [x] Campana `App\Livewire\NotificationBell` (+ `resources/views/livewire/notification-bell.blade.php`):
  badge de no leídas, `wire:poll.30s`, `markAsRead` (marca + navega), `markAllAsRead`.
- [x] **Comando** `notifications:escanear-eventos` (`App\Console\Commands\ScanEventNotifications`),
  idempotente vía `events.reminder_notified_at` / `ending_notified_at` (migración `2026_06_25_000003`).
- [x] **Retención** `notifications:limpiar` (`PruneNotifications`, borra leídas viejas).
- [x] **Config** `config/notifications.php` (ventanas de aviso y retención, vía `.env`).
- [x] **Schedule** en `routes/console.php` (escaneo c/5 min, limpiezas diarias, `queue:work` c/min).
- [x] Tests `NotificationCenterTest` (campana muestra/marca, escaneo crea e idempotente, retención).

**Decisiones tomadas (afinan la propuesta):**
- **Campana = una sola instancia**, fija arriba a la derecha (`fixed top-3 right-16 lg:right-4 z-50`),
  visible en todos los tamaños (en móvil se corre a la izquierda del perfil del header). Se descartó
  montarla dos veces (escritorio + header móvil) porque **duplicaba el coste de Livewire por página**
  y rompía `PagePerformanceTest` en `/usuarios`. Por la misma razón, la **lista se carga perezosamente**
  al abrir el desplegable (`loadItems`); el `mount` solo hace un `COUNT` indexado de no leídas.
- **Campos reales del evento:** `date` + `start_time` / `end_time` (no `start_date`/`end_date`); el
  cálculo fecha+hora se hace en PHP (`Event::startsAt()` / `endsAt()`) para ser portable SQLite/MySQL.
- **Idempotencia:** se eligieron columnas `*_notified_at` en `events` (más baratas que consultar
  `notifications`).
- **Ventanas por defecto:** 60 min ("próximo"), 30 min ("por finalizar"); poll 30s; retención 30 días.
- **Destinatario:** el responsable del evento (`Event->user`) — alcance a admins de sede queda abierto.

## Pendiente (opcional)
- [ ] Página `/notificaciones` con historial paginado completo.
- [ ] Segundo canal `mail` reutilizando los mismos `Notification` (si se quiere aviso por correo).
- [ ] Revisar que la campana fija no tape acciones en la esquina superior derecha de algunas vistas.
- [ ] Destinatarios extendidos (admins de la sede además del responsable).

## Relacionado
[[adr-0004-pasarela-de-revision-para-importacion-de-participantes]] · [[mapa-de-modulos]] ·
[[modelo-de-datos]] · [[migracion-multi-sede]] · [[roadmap]] · [[ideas]]
