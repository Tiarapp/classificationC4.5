<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Respondent;
use App\Models\Dataset;
use App\Models\TrainingSession;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $adminUser = User::create([
            'name' => 'Administrator',
            'email' => 'admin@tiktok.test',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
        ]);

        // // Create sample respondents
        // $respondents = [
        //     ['nama' => 'Ahmad Rifai', 'nim' => '2019001', 'jurusan' => 'Teknik Informatika', 'semester' => 6],
        //     ['nama' => 'Siti Nurhaliza', 'nim' => '2019002', 'jurusan' => 'Sistem Informasi', 'semester' => 6],
        //     ['nama' => 'Budi Santoso', 'nim' => '2020001', 'jurusan' => 'Teknik Informatika', 'semester' => 4],
        //     ['nama' => 'Dewi Sartika', 'nim' => '2020002', 'jurusan' => 'Manajemen Informatika', 'semester' => 4],
        //     ['nama' => 'Eko Prasetyo', 'nim' => '2021001', 'jurusan' => 'Teknik Informatika', 'semester' => 2],
        // ];

        // foreach ($respondents as $respondentData) {
        //     $respondent = Respondent::create($respondentData);

        //     // Create sample datasets for each respondent
        //     $datasets = [
        //         [
        //             'respondent_id' => $respondent->id,
        //             'durasi_penggunaan' => collect(['<=1 jam', '1-3 jam', '3-5 jam', '>5 jam'])->random(),
        //             'frekuensi_akses' => collect(['1-2x', '3-5x', '>5x'])->random(),
        //             'perhatian_konten' => rand(1, 5),
        //             'penghayatan' => rand(1, 5),
        //             'label_intensitas' => collect(['rendah', 'sedang', 'tinggi'])->random(),
        //             'is_training_data' => true,
        //         ],
        //         [
        //             'respondent_id' => $respondent->id,
        //             'durasi_penggunaan' => collect(['<=1 jam', '1-3 jam', '3-5 jam', '>5 jam'])->random(),
        //             'frekuensi_akses' => collect(['1-2x', '3-5x', '>5x'])->random(),
        //             'perhatian_konten' => rand(1, 5),
        //             'penghayatan' => rand(1, 5),
        //             'label_intensitas' => collect(['rendah', 'sedang', 'tinggi'])->random(),
        //             'is_training_data' => false,
        //         ],
        //     ];

        //     foreach ($datasets as $datasetData) {
        //         Dataset::create($datasetData);
        //     }
        // }

        // // Create sample training session
        // TrainingSession::create([
        //     'algorithm' => 'C4.5',
        //     'parameters' => [
        //         'min_samples_split' => 2,
        //         'min_samples_leaf' => 1,
        //         'max_depth' => null
        //     ],
        //     'train_data_count' => 55,
        //     'test_data_count' => 45,
        //     'accuracy' => 0.8500,
        //     'training_time' => 2.1500,
        //     'model_data' => [
        //         'type' => 'split',
        //         'attribute' => 'penghayatan',
        //         'entropy' => 1.2850,
        //         'gain_ratio' => 0.4521,
        //         'samples' => 55,
        //         'children' => [
        //             '1' => ['type' => 'leaf', 'class' => 'rendah', 'samples' => 10, 'confidence' => 0.85],
        //             '2' => ['type' => 'leaf', 'class' => 'rendah', 'samples' => 8, 'confidence' => 0.80],
        //             '3' => [
        //                 'type' => 'split',
        //                 'attribute' => 'perhatian_konten',
        //                 'entropy' => 0.9183,
        //                 'gain_ratio' => 0.3214,
        //                 'samples' => 25,
        //                 'children' => [
        //                     '1' => ['type' => 'leaf', 'class' => 'sedang', 'samples' => 6, 'confidence' => 0.75],
        //                     '2' => ['type' => 'leaf', 'class' => 'sedang', 'samples' => 8, 'confidence' => 0.82],
        //                     '3' => ['type' => 'leaf', 'class' => 'sedang', 'samples' => 7, 'confidence' => 0.78],
        //                     '4' => ['type' => 'leaf', 'class' => 'tinggi', 'samples' => 2, 'confidence' => 0.65],
        //                     '5' => ['type' => 'leaf', 'class' => 'tinggi', 'samples' => 2, 'confidence' => 0.65]
        //                 ]
        //             ],
        //             '4' => ['type' => 'leaf', 'class' => 'tinggi', 'samples' => 8, 'confidence' => 0.88],
        //             '5' => ['type' => 'leaf', 'class' => 'tinggi', 'samples' => 4, 'confidence' => 0.95]
        //         ]
        //     ]
        // ]);

        // echo "Seeder completed successfully!\n";
        // echo "Admin login credentials:\n";
        // echo "Email: admin@tiktok.test\n";
        // echo "Password: admin123\n";
    }
}
