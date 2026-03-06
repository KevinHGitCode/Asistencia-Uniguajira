# Convenciones de Tests — Asistencia Uniguajira

## Ejecutar los tests

```bash
composer run test
# o
php artisan test

# Ejecutar un módulo específico
php artisan test tests/Feature/Dashboard
php artisan test tests/Feature/Statistics
php artisan test tests/Feature/Users

# Ejecutar un solo archivo
php artisan test tests/Feature/Dashboard/DashboardStatsTest.php
```

---

## Estructura de carpetas

```
tests/
├── Unit/
│   └── Models/
│       └── UserModelTest.php          ← test de modelo (reglas, relaciones, métodos)
├── Feature/
│   ├── Dashboard/
│   │   ├── DashboardAccessTest.php    ← control de acceso
│   │   ├── DashboardGreetingTest.php  ← saludo personalizado
│   │   ├── DashboardStatsTest.php     ← estadísticas del dashboard
│   │   └── DashboardCalendarTest.php  ← endpoints del calendario
│   ├── Statistics/
│   │   ├── Concerns/
│   │   │   └── HasStatisticsScenario.php  ← trait con datos compartidos
│   │   ├── StatisticsAccessTest.php   ← control de acceso a las páginas
│   │   ├── StatisticsAsistenciasTest.php  ← módulo por asistencias
│   │   ├── StatisticsParticipantesTest.php ← módulo por participantes
│   │   ├── StatisticsCoherenceTest.php    ← invariantes entre módulos
│   │   ├── StatisticsFiltersTest.php      ← filtros de fecha
│   │   └── StatisticsUsuariosTest.php     ← módulo por usuarios (solo admin)
│   └── Users/
│       ├── UserIndexTest.php          ← listado de usuarios
│       ├── UserCreateTest.php         ← creación y validaciones
│       ├── UserUpdateTest.php         ← edición
│       ├── UserDeleteTest.php         ← eliminación con contraseña
│       └── UserShowTest.php           ← vista de detalle
```

### Convención por módulo

Cada módulo tiene su propia carpeta en `tests/Feature/<Modulo>/`.
Dentro de esa carpeta se crean archivos separados por responsabilidad:

| Archivo | Qué verifica |
|---|---|
| `<Modulo>AccessTest.php` | Acceso por rol, redirección de invitados |
| `<Modulo>StatsTest.php` | Lógica de conteos y datos de la vista |
| `<Modulo>FiltersTest.php` | Filtros por fecha, usuario, etc. |
| `<Modulo>CoherenceTest.php` | Invariantes entre endpoints |
| `<Modulo>GreetingTest.php` | Textos o mensajes dinámicos |

Si hay datos de escenario reutilizables entre varios archivos de un módulo,
se encapsulan en un trait dentro de `<Modulo>/Concerns/Has<Modulo>Scenario.php`.

---

## Factories requeridas

| Factory | Modelo | Notas |
|---|---|---|
| `UserFactory` | `User` | Incluida por defecto en Laravel |
| `EventFactory` | `Event` | Requiere `user_id` en creación |
| `DependencyFactory` | `Dependency` | Creada manualmente |
| `ProgramFactory` | `Program` | Creada manualmente |
| `ParticipantFactory` | `Participant` | Creada manualmente; requiere `program_id` |
| `AttendanceFactory` | `Attendance` | Creada manualmente; requiere `event_id` y `participant_id` |

---

## Principios generales

### RefreshDatabase
Todos los tests de feature y unidad usan `RefreshDatabase` para aislar
completamente los datos entre pruebas.

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MiTest extends TestCase
{
    use RefreshDatabase;
}
```

### actingAs para autenticación
```php
$admin = User::factory()->create(['role' => 'admin']);
$this->actingAs($admin)->get(route('ruta'))->assertOk();
```

### Congelar el tiempo con Carbon
Cuando los endpoints dependen de `now()` (ej. lógica de semestre del calendario),
congelar el tiempo garantiza resultados predecibles:

```php
use Carbon\Carbon;

protected function setUp(): void
{
    parent::setUp();
    Carbon::setTestNow('2026-03-01');
}

protected function tearDown(): void
{
    Carbon::setTestNow();
    parent::tearDown();
}
```

### Datos fijos vs faker
- Usar fechas fijas explícitas (`'date' => '2026-02-01'`) cuando el test depende
  de rangos de fecha, no fechas del faker.
- Para campos irrelevantes al test (título, descripción), el faker es aceptable.

### markTestSkipped para incompatibilidades de BD
Si un endpoint usa funciones MySQL (ej. `DATE_FORMAT`), usar skip explícito:

```php
if (config('database.default') === 'sqlite') {
    $this->markTestSkipped('DATE_FORMAT() requiere MySQL.');
}
```

---

## Notas de la arquitectura

### Diferencia asistencias vs participantes
- **Asistencias** (`/api/statistics/total-attendances`): `COUNT(*)` — cada registro de asistencia cuenta independientemente.
- **Participantes** (`/api/statistics/total-participants`): `COUNT(DISTINCT participants.id)` — una persona que asistió N veces cuenta como 1.

### Roles
- `admin` — acceso completo, incluyendo módulo "Por Usuarios" de estadísticas
- `user`  — acceso restringido: ve solo sus propios datos en el dashboard y en eventos

### API de estadísticas
Las rutas `/api/statistics/*` son **públicas** (sin auth) — comportamiento actual documentado
como bug. Los tests verifican el comportamiento existente.

### Módulo "Por Usuarios" de estadísticas
Requiere `role:admin` en la ruta web (`estadisticas/usuarios`).
