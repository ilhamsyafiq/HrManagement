<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;

        $holidays = [
            ['name' => 'New Year\'s Day', 'date' => "$year-01-01", 'type' => 'Public', 'is_recurring' => true],
            ['name' => 'Thaipusam', 'date' => "$year-01-25", 'type' => 'Public', 'is_recurring' => false],
            ['name' => 'Nuzul Al-Quran', 'date' => "$year-03-17", 'type' => 'Public', 'is_recurring' => false],
            ['name' => 'Labour Day', 'date' => "$year-05-01", 'type' => 'Public', 'is_recurring' => true],
            ['name' => 'Wesak Day', 'date' => "$year-05-12", 'type' => 'Public', 'is_recurring' => false],
            ['name' => 'Yang di-Pertuan Agong Birthday', 'date' => "$year-06-02", 'type' => 'Public', 'is_recurring' => false],
            ['name' => 'Hari Raya Aidilfitri', 'date' => "$year-03-30", 'type' => 'Public', 'is_recurring' => false],
            ['name' => 'Hari Raya Aidilfitri (2nd Day)', 'date' => "$year-03-31", 'type' => 'Public', 'is_recurring' => false],
            ['name' => 'Hari Raya Haji', 'date' => "$year-06-07", 'type' => 'Public', 'is_recurring' => false],
            ['name' => 'Awal Muharram', 'date' => "$year-06-27", 'type' => 'Public', 'is_recurring' => false],
            ['name' => 'Malaysia Day', 'date' => "$year-09-16", 'type' => 'Public', 'is_recurring' => true],
            ['name' => 'Maulidur Rasul', 'date' => "$year-09-05", 'type' => 'Public', 'is_recurring' => false],
            ['name' => 'Deepavali', 'date' => "$year-10-20", 'type' => 'Public', 'is_recurring' => false],
            ['name' => 'Christmas Day', 'date' => "$year-12-25", 'type' => 'Public', 'is_recurring' => true],
            ['name' => 'National Day', 'date' => "$year-08-31", 'type' => 'Public', 'is_recurring' => true],
            ['name' => 'Company Anniversary', 'date' => "$year-07-15", 'type' => 'Company', 'is_recurring' => true, 'description' => 'Company founding anniversary'],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(
                ['name' => $holiday['name'], 'date' => $holiday['date']],
                $holiday
            );
        }
    }
}
