<?php

namespace PERP\Utilities\Database;

use Illuminate\Database\Schema\Blueprint;

class Migration
{
    public static function generate(Blueprint $table, array $fields, array $indexes = [])
    {
        array_map(function ($field) use ($table) {
            return self::getMigrationSchema($table, $field);
        }, $fields);

        if (!empty($indexes)) {
            array_map(function ($index) use ($table) {
                return $table->index($index);
            }, $indexes);
        }
    }

    private function getMigrationSchema(Blueprint $table, $field)
    {
        switch ($field['type']) {
            case 'string':
                return $table->string($field['title'], (isset($field['length']) ? $field['length'] : 255))
                    ->nullable((isset($field['is_null']) ? $field['is_null'] : false))
                    ->default((isset($field['default']) ? $field['default'] : ''));
                break;

            default:
                return $table->string($field['title'], (isset($field['length']) ? $field['length'] : 255))
                    ->nullable((isset($field['is_null']) ? $field['is_null'] : false))
                    ->default((isset($field['default']) ? $field['default'] : ''));
                break;
        }
    }
}
