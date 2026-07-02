<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class AcademicSemesterService
{
    public function currentPeriod(): array
    {
        return $this->periodForDate(CarbonImmutable::now());
    }

    public function periodFromRequest(Request $request): array
    {
        $default = $this->currentPeriod();
        $year = (int) $request->integer('year', $default['year']);
        $semester = (int) $request->integer('semester', $default['semester']);

        if (! array_key_exists($semester, $this->semesters())) {
            $semester = $default['semester'];
        }

        return [
            'year' => $year,
            'semester' => $semester,
        ];
    }

    public function periodForDate(CarbonImmutable $date): array
    {
        foreach ($this->semesters() as $number => $semester) {
            if ($date->month >= $semester['start_month'] && $date->month <= $semester['end_month']) {
                return [
                    'year' => $date->year,
                    'semester' => (int) $number,
                ];
            }
        }

        return [
            'year' => $date->year,
            'semester' => 1,
        ];
    }

    public function bounds(array $period): array
    {
        $semester = $this->semesterConfig((int) $period['semester']);
        $year = (int) $period['year'];
        $start = CarbonImmutable::create($year, (int) $semester['start_month'], 1)->startOfDay();
        $end = CarbonImmutable::create($year, (int) $semester['end_month'], 1)->endOfMonth()->endOfDay();

        return [$start, $end];
    }

    public function adjacentPeriod(array $period, int $step): array
    {
        $semesters = array_keys($this->semesters());
        sort($semesters);

        $currentIndex = array_search((int) $period['semester'], $semesters, true);
        $targetIndex = $currentIndex + $step;
        $year = (int) $period['year'];

        if ($targetIndex < 0) {
            $targetIndex = count($semesters) - 1;
            $year--;
        } elseif ($targetIndex >= count($semesters)) {
            $targetIndex = 0;
            $year++;
        }

        return [
            'year' => $year,
            'semester' => (int) $semesters[$targetIndex],
        ];
    }

    public function metadata(array $period): array
    {
        [$start, $end] = $this->bounds($period);
        $semester = $this->semesterConfig((int) $period['semester']);

        return [
            'year' => (int) $period['year'],
            'semester' => (int) $period['semester'],
            'label' => 'Semestre '.$semester['label'].' '.(int) $period['year'],
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'range' => $start->diffInMonths($end->startOfMonth()) + 1,
        ];
    }

    private function semesterConfig(int $semester): array
    {
        return $this->semesters()[$semester] ?? $this->semesters()[1];
    }

    private function semesters(): array
    {
        return config('academic.semesters', []);
    }
}
