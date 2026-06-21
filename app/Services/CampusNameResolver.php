<?php

namespace App\Services;

use App\Models\Campus;
use Illuminate\Support\Collection;

class CampusNameResolver
{
    /**
     * Identifica una sede únicamente cuando el último segmento tras un guion
     * coincide con una sede configurada. Así, "- Danza" no se interpreta como sede.
     *
     * @param  Collection<int, Campus>  $campuses
     */
    public function resolve(string $name, Collection $campuses): ?Campus
    {
        $suffix = $this->suffix($name);
        if ($suffix === null) {
            return null;
        }

        $suffix = $this->comparisonKey($suffix);

        return $campuses->first(
            fn (Campus $campus) => $this->comparisonKey($campus->name) === $suffix
        );
    }

    public function withoutSuffix(string $name): string
    {
        return trim((string) preg_replace('/\s*-\s*[^-]+$/u', '', $name));
    }

    public function suffix(string $name): ?string
    {
        if (! preg_match('/-\s*([^-]+)\s*$/u', trim($name), $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    /**
     * Detecta una sede mencionada como palabra completa en el nombre.
     * Solo se usa para normalizar los datos históricos del superadmin.
     *
     * @param  Collection<int, Campus>  $campuses
     */
    public function resolveMentioned(string $name, Collection $campuses): ?Campus
    {
        return $campuses
            ->sortByDesc(fn (Campus $campus) => mb_strlen($campus->name, 'UTF-8'))
            ->first(fn (Campus $campus) => preg_match(
                '/(?<![\pL\pN])'.preg_quote($campus->name, '/').'(?![\pL\pN])/iu',
                $name
            ) === 1);
    }

    private function comparisonKey(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');

        if (class_exists(\Normalizer::class)) {
            $value = \Normalizer::normalize($value, \Normalizer::FORM_D) ?: $value;
            $value = preg_replace('/\pM/u', '', $value) ?? $value;
        }

        return preg_replace('/\s+/u', ' ', $value) ?? $value;
    }
}
