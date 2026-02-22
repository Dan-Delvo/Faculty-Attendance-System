<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class Controller
{
    public function extract_enums(string $table, string $column): array
    {
        $type = DB::select(DB::raw("SHOW COLUMNS FROM {$table} WHERE Field = '{$column}'"))[0]->Type;

        preg_match('/^enum\((.*)\)$/', $type, $matches);

        if (!isset($matches[1])) {
            return [];
        }

        return array_map(fn ($value) => trim($value, "'"), explode(',', $matches[1]));
    }
}
