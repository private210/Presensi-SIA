<?php
// database/seeders/RoleSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = Role::create(['name' => 'super_admin']);
        // Buat role wali kelas
        $waliKelasRole = Role::create(['name' => 'Wali Kelas']);

        // Buat role wali murid
        $waliMuridRole = Role::create(['name' => 'Wali Murid']);

        // Buat role kepala sekolah
        $kepalaSekolahRole = Role::create(['name' => 'Kepala Sekolah']);
    }
}
