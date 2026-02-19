<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HolidaySeeder extends Seeder
{
    /**
     * Philippine public holidays for 2026.
     * Unique key: holiday_date — skipped if already present.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $holidays = [
                // ── Regular holidays ──────────────────────────────────────────
                ['holiday_date' => '2026-01-01', 'name' => "New Year's Day",                 'type' => 'national', 'is_recurring' => true],
                ['holiday_date' => '2026-04-02', 'name' => 'Maundy Thursday',                'type' => 'national', 'is_recurring' => false],
                ['holiday_date' => '2026-04-03', 'name' => 'Good Friday',                    'type' => 'national', 'is_recurring' => false],
                ['holiday_date' => '2026-04-04', 'name' => 'Black Saturday',                 'type' => 'national', 'is_recurring' => false],
                ['holiday_date' => '2026-04-09', 'name' => 'Araw ng Kagitingan',             'type' => 'national', 'is_recurring' => true],
                ['holiday_date' => '2026-05-01', 'name' => 'Labor Day',                      'type' => 'national', 'is_recurring' => true],
                ['holiday_date' => '2026-06-12', 'name' => 'Independence Day',               'type' => 'national', 'is_recurring' => true],
                ['holiday_date' => '2026-08-28', 'name' => 'National Heroes Day',            'type' => 'national', 'is_recurring' => false],
                ['holiday_date' => '2026-11-30', 'name' => "Bonifacio Day",                  'type' => 'national', 'is_recurring' => true],
                ['holiday_date' => '2026-12-25', 'name' => 'Christmas Day',                  'type' => 'national', 'is_recurring' => true],
                ['holiday_date' => '2026-12-30', 'name' => "Rizal Day",                      'type' => 'national', 'is_recurring' => true],
                // ── Special non-working holidays ──────────────────────────────
                ['holiday_date' => '2026-02-25', 'name' => 'EDSA People Power Revolution',  'type' => 'special',  'is_recurring' => true],
                ['holiday_date' => '2026-03-31', 'name' => "Eid'l Fitr (approx.)",          'type' => 'national', 'is_recurring' => false],
                ['holiday_date' => '2026-04-01', 'name' => 'Araw ng Dabaw (local)',          'type' => 'local',    'is_recurring' => false],
                ['holiday_date' => '2026-08-21', 'name' => 'Ninoy Aquino Day',               'type' => 'special',  'is_recurring' => true],
                ['holiday_date' => '2026-11-01', 'name' => "All Saints' Day",               'type' => 'special',  'is_recurring' => true],
                ['holiday_date' => '2026-11-02', 'name' => "All Souls' Day",                'type' => 'special',  'is_recurring' => true],
                ['holiday_date' => '2026-12-08', 'name' => 'Feast of the Immaculate Conception', 'type' => 'special', 'is_recurring' => true],
                ['holiday_date' => '2026-12-24', 'name' => 'Christmas Eve',                 'type' => 'special',  'is_recurring' => true],
                ['holiday_date' => '2026-12-31', 'name' => "New Year's Eve",                'type' => 'special',  'is_recurring' => true],
            ];

            foreach ($holidays as $holiday) {
                Holiday::firstOrCreate(['holiday_date' => $holiday['holiday_date']], $holiday);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
