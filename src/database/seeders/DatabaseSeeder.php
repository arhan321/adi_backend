<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Jalankan seluruh seeder aplikasi.
     */
    public function run(): void
    {
        /*
         * Admin default dari coding lama tetap dipertahankan.
         * firstOrCreate mencegah email admin dibuat berulang kali.
         */
        User::query()->firstOrCreate(
            [
                'email' => 'admin@admin.com',
            ],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ],
        );

        /*
         * Memuat file CrmRoleSeeder secara langsung.
         * Ini menjadi fallback apabila autoload Composer belum mendeteksinya.
         */
        require_once database_path('seeders/CrmRoleSeeder.php');

        /*
         * Karena DatabaseSeeder dan CrmRoleSeeder berada dalam
         * namespace Database\Seeders, class bisa langsung dipanggil.
         */
        $this->call([
            CrmRoleSeeder::class,
        ]);
    }
}