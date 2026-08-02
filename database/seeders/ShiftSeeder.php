<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Normal Shift',
                'start_time' => '09:00',
                'end_time' => '17:30',
                'break_start' => '13:00',
                'break_end' => '14:00',
                'description' => 'Standard working day (7.5h paid).',
            ],
            [
                'name' => 'Night Shift',
                'start_time' => '21:00',
                'end_time' => '06:00',
                'break_start' => '01:00',
                'break_end' => '02:00',
                'description' => 'Overnight shift (crosses midnight). Adjust times to your real night hours.',
            ],
            [
                'name' => 'Friday – Male',
                'start_time' => '09:00',
                'end_time' => '17:30',
                'break_start' => '12:30',
                'break_end' => '14:30',
                'description' => 'Friday schedule for men: 09:00-12:30 and 14:30-17:30, with a 12:30-14:30 prayer break (6.5h paid).',
            ],
            [
                'name' => 'Friday – Female',
                'start_time' => '09:00',
                'end_time' => '16:00',
                'break_start' => '13:00',
                'break_end' => '14:00',
                'description' => 'Friday schedule for women: 09:00-16:00 with a 13:00-14:00 lunch break (6h paid).',
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::updateOrCreate(
                ['name' => $shift['name']],
                array_merge($shift, ['is_active' => true]),
            );
        }
    }
}
