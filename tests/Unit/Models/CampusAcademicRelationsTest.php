<?php

namespace Tests\Unit\Models;

use App\Models\AcademicProgram;
use App\Models\Area;
use App\Models\Campus;
use App\Models\Dependency;
use App\Models\Event;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class CampusAcademicRelationsTest extends TestCase
{
    public function test_core_models_belong_to_campus(): void
    {
        $this->assertBelongsToCampus(new User);
        $this->assertBelongsToCampus(new Event);
        $this->assertBelongsToCampus(new Dependency);
        $this->assertBelongsToCampus(new Program);
        $this->assertBelongsToCampus(new Area);
    }

    public function test_campus_has_many_core_models(): void
    {
        $campus = new Campus;

        $this->assertHasMany($campus->users(), User::class);
        $this->assertHasMany($campus->events(), Event::class);
        $this->assertHasMany($campus->dependencies(), Dependency::class);
        $this->assertHasMany($campus->programs(), Program::class);
        $this->assertHasMany($campus->areas(), Area::class);
    }

    public function test_program_belongs_to_academic_program(): void
    {
        $relation = (new Program)->academicProgram();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertSame(AcademicProgram::class, $relation->getRelated()::class);
        $this->assertSame('academic_program_id', $relation->getForeignKeyName());
    }

    public function test_academic_program_has_many_programs(): void
    {
        $relation = (new AcademicProgram)->programs();

        $this->assertHasMany($relation, Program::class);
    }

    private function assertBelongsToCampus(object $model): void
    {
        $relation = $model->campus();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertSame(Campus::class, $relation->getRelated()::class);
        $this->assertSame('campus_id', $relation->getForeignKeyName());
    }

    private function assertHasMany(HasMany $relation, string $relatedModel): void
    {
        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertSame($relatedModel, $relation->getRelated()::class);
    }
}
