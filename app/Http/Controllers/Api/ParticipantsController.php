<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Affiliation;
use App\Models\Dependency;
use App\Models\Participant;
use App\Models\ParticipantType;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Listado de participantes para la isla React (ADR-0008).
 *
 * Replica la búsqueda, filtros y paginación que tenía el componente Livewire
 * `Admin\ParticipantsList`, pero del lado del servidor vía JSON. Los participantes
 * son globales (no se filtran por sede — ver migracion-multi-sede).
 */
class ParticipantsController extends Controller
{
    /**
     * GET /api/participants
     *   ?page=1&perPage=25&search=ana
     *   &estamento=3&programa=5&dependencia=2&vinculacion=1
     *   &correo=con|sin&sinClasificar=1
     */
    public function index(Request $request)
    {
        $perPage = max(5, min((int) $request->integer('perPage', 25), 100));

        $search      = trim((string) $request->query('search', ''));
        $type        = (string) $request->query('estamento', '');
        $program     = (string) $request->query('programa', '');
        $dependency  = (string) $request->query('dependencia', '');
        $affiliation = (string) $request->query('vinculacion', '');
        $email       = (string) $request->query('correo', '');
        $unclassified = $request->boolean('sinClasificar');

        $query = Participant::query()
            ->with([
                'activeRoles.type:id,name',
                'activeRoles.program:id,name',
                'activeRoles.dependency:id,name',
                'activeRoles.affiliation:id,name',
            ])
            ->when($search !== '', function ($q) use ($search) {
                $term = '%'.$search.'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('document', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            });

        // ── Filtros estructurados sobre roles activos (AND dentro del mismo rol) ──
        if ($type !== '' || $program !== '' || $dependency !== '' || $affiliation !== '') {
            $query->whereHas('activeRoles', function ($r) use ($type, $program, $dependency, $affiliation) {
                $r->when($type !== '', fn ($x) => $x->where('participant_type_id', $type))
                  ->when($program !== '', fn ($x) => $x->where('program_id', $program))
                  ->when($dependency !== '', fn ($x) => $x->where('dependency_id', $dependency))
                  ->when($affiliation !== '', fn ($x) => $x->where('affiliation_id', $affiliation));
            });
        }

        // ── Con / sin correo ──
        if ($email === 'con') {
            $query->whereNotNull('email')->where('email', '!=', '');
        } elseif ($email === 'sin') {
            $query->where(fn ($q) => $q->whereNull('email')->orWhere('email', '=', ''));
        }

        // ── Sin clasificar (al menos una asistencia con rol sin programa/dependencia/organización) ──
        if ($unclassified) {
            $query->whereIn('id', $this->unclassifiedParticipantIds());
        }

        $participants = $query
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate($perPage)
            ->withQueryString();

        $data = $participants->getCollection()->map(function (Participant $p) {
            $roles = $p->activeRoles;

            return [
                'id'                    => $p->id,
                'document'              => $p->document,
                'student_code'          => $p->student_code,
                'first_name'            => $p->first_name,
                'last_name'             => $p->last_name,
                'email'                 => $p->email,
                'types'                 => $roles->pluck('type.name')->filter()->unique()->values(),
                'programs'              => $roles->pluck('program.name')->filter()->unique()->values(),
                'dependencies'          => $roles->pluck('dependency.name')->filter()->unique()->values(),
                'affiliations'          => $roles->pluck('affiliation.name')->filter()->unique()->values(),
                'has_unclassified_role' => $roles->contains(
                    fn ($r) => is_null($r->program_id) && is_null($r->dependency_id) && is_null($r->organization_id)
                ),
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $participants->currentPage(),
                'last_page'    => $participants->lastPage(),
                'per_page'     => $participants->perPage(),
                'total'        => $participants->total(),
                'from'         => $participants->firstItem(),
                'to'           => $participants->lastItem(),
            ],
        ]);
    }

    /**
     * GET /api/participants/filter-options
     * Catálogos para los selects de filtro.
     */
    public function filterOptions()
    {
        return response()->json([
            'types'        => ParticipantType::orderBy('name')->get(['id', 'name']),
            'programs'     => Program::orderBy('name')->get(['id', 'name']),
            'affiliations' => Affiliation::orderBy('name')->get(['id', 'name']),
            'dependencies' => Dependency::with('campus:id,name')
                ->orderBy('name')
                ->get(['id', 'name', 'campus_id'])
                ->map(fn (Dependency $d) => [
                    'id'   => $d->id,
                    'name' => $d->name.($d->campus?->name ? ' - '.$d->campus->name : ''),
                ])
                ->values(),
        ]);
    }

    /**
     * IDs de participantes con al menos una asistencia cuyo rol no tiene
     * programa, dependencia ni organización asignados.
     */
    private function unclassifiedParticipantIds()
    {
        return DB::table('participants')
            ->join('attendances', 'attendances.participant_id', '=', 'participants.id')
            ->leftJoin('attendance_details', 'attendance_details.attendance_id', '=', 'attendances.id')
            ->leftJoin('participant_roles', 'participant_roles.id', '=', 'attendance_details.participant_role_id')
            ->where(function ($q) {
                $q->whereNull('attendance_details.id')
                  ->orWhereNull('attendance_details.participant_role_id')
                  ->orWhere(function ($q2) {
                      $q2->whereNull('participant_roles.program_id')
                         ->whereNull('participant_roles.dependency_id')
                         ->whereNull('participant_roles.organization_id');
                  });
            })
            ->distinct()
            ->pluck('participants.id');
    }
}
