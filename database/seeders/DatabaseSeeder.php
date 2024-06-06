<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\StaffModel;

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
            'phone'=>'1234567890',
            'password'=>bcrypt(123456),

        ];

        Admin::create($admin);

         $techer=[
            'name'=> 'teacher',
            'email'=>'teacher@gmail.com',
            'phone'=>'1234567890',
            'password'=>bcrypt(123456),

        ];

        Teacher::create($techer);

         $student=[
            'name'=> 'student',
            'phone'=>'1234567890',
            'email'=>'student@gmail.com',
            'password'=>bcrypt(123456),

        ];
        Student::create($student);

         $staff=[
            'name'=> 'staff',
            'phone'=>'1234567890',
            'email'=>'staff@gmail.com',
            'password'=>bcrypt(123456),

        ];
        StaffModel::create($staff);

        
    }
}
