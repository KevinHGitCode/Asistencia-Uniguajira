<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Normaliza distintas formas de "opciones" a una lista uniforme de
 * ['value' => string, 'label' => string] para los selectores con búsqueda
 * (x-ui.searchable-select / x-ui.multi-searchable-select).
 *
 * Acepta:
 *  - Array asociativo  ['admin' => 'Administrador', ...]   → clave=valor, valor=etiqueta
 *  - Lista de arrays   [['id'=>1,'name'=>'X'], ...]
 *  - Lista de objetos  [Model{id,name}, ...] (p.ej. Eloquent)
 *  - Lista de escalares ['Pregrado', 'Posgrado', ...]
 */
class SelectOptions
{
    public static function normalize(mixed $options, string $valueKey = 'id', string $labelKey = 'name'): array
    {
        $raw = $options instanceof Collection ? $options->all() : (array) $options;

        if ($raw === []) {
            return [];
        }

        // Array asociativo: la clave es el valor y el contenido la etiqueta.
        if (! array_is_list($raw)) {
            $out = [];
            foreach ($raw as $key => $label) {
                $out[] = ['value' => (string) $key, 'label' => (string) $label];
            }

            return $out;
        }

        return array_map(static function ($o) use ($valueKey, $labelKey) {
            if (is_array($o)) {
                return [
                    'value' => (string) ($o[$valueKey] ?? $o['value'] ?? ''),
                    'label' => (string) ($o[$labelKey] ?? $o['label'] ?? ''),
                ];
            }

            if (is_object($o)) {
                return [
                    'value' => (string) ($o->{$valueKey} ?? $o->value ?? ''),
                    'label' => (string) ($o->{$labelKey} ?? $o->label ?? ''),
                ];
            }

            return ['value' => (string) $o, 'label' => (string) $o];
        }, $raw);
    }
}
