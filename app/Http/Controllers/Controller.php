<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class Controller
{
    public function extract_enums(string $table, string $column): array
    {
        if (!preg_match('/^\w+$/', $table) || !preg_match('/^\w+$/', $column)) {
            throw new \InvalidArgumentException('Invalid table or column name.');
        }

        $safeTable = '`' . str_replace('`', '``', $table) . '`';

        $result = DB::select("SHOW COLUMNS FROM {$safeTable} WHERE Field = ?", [$column]);

        if (empty($result)) {
            return [];
        }

        $type = $result[0]->Type;
        preg_match('/^enum\((.*)\)$/', $type, $matches);

        if (!isset($matches[1])) {
            return [];
        }

        return array_map(fn ($value) => trim($value, "'"), explode(',', $matches[1]));
    }
}
