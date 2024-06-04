<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Teacher;
use App\Models\Student;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $admin=[
            'name'=> 'admin',
            'email'=>'admin@gmail.com',
            'password'=>bcrypt(123456),

        ];

        Admin::create($admin);

         $techer=[
            'name'=> 'teacher',
            'email'=>'teacher@gmail.com',
            'password'=>bcrypt(123456),

        ];

        Teacher::create($techer);

         $student=[
            'name'=> 'student',
            'email'=>'student@gmail.com',
            'password'=>bcrypt(123456),

        ];
        Student::create($student);
    }
}
