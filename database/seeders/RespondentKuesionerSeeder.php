<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Respondent;
use App\Models\Dataset;
use Faker\Factory as Faker;

class RespondentKuesionerSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');
        
        // Daftar jurusan di universitas
        $jurusan = [
            'Teknik Informatika', 'Sistem Informasi', 'Teknik Komputer',
            'Manajemen', 'Akuntansi', 'Ekonomi Pembangunan',
            'Hukum', 'Ilmu Komunikasi', 'Psikologi',
            'Teknik Sipil', 'Teknik Mesin', 'Teknik Elektro',
            'Kedokteran', 'Keperawatan', 'Farmasi',
            'Pendidikan Bahasa Inggris', 'Pendidikan Matematika', 'PGSD',
            'Desain Grafis', 'Seni Rupa', 'Arsitektur'
        ];

        // Generate 100 responden dengan kuesioner
        for ($i = 1; $i <= 100; $i++) {
            // Create responden
            $responden = Respondent::create([
                'nama' => $faker->name,
                'nim' => $this->generateNIM($faker),
                'jurusan' => $faker->randomElement($jurusan),
                'semester' => $faker->numberBetween(1, 8)
            ]);

            // Generate kuesioner data yang realistic
            $this->generateKuesionerData($responden, $faker);
        }

        $this->command->info('✅ Berhasil generate 100 responden dengan kuesioner lengkap');
    }

    /**
     * Generate NIM yang realistic
     */
    private function generateNIM($faker)
    {
        $tahun = $faker->randomElement(['20', '21', '22', '23', '24']);
        $kode = $faker->randomElement(['01', '02', '03', '04', '05']);
        $nomor = str_pad($faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT);
        
        return $tahun . $kode . $nomor;
    }

    /**
     * Generate data kuesioner yang realistic berdasarkan pola penggunaan TikTok
     */
    private function generateKuesionerData(Respondent $responden, $faker)
    {
        // Tentukan intensitas terlebih dahulu dengan distribusi yang baik
        $intensitasDistribution = [
            'rendah' => 40,   // 40%
            'sedang' => 35,   // 35%  
            'tinggi' => 25    // 25%
        ];
        
        $intensitas = $this->weightedRandom($intensitasDistribution, $faker);
        
        // Generate data berdasarkan intensitas untuk konsistensi
        switch ($intensitas) {
            case 'rendah':
                $durasi = $this->weightedRandom([
                    '<=1 jam' => 60,
                    '1-3 jam' => 35,
                    '3-5 jam' => 5,
                    '>5 jam' => 0
                ], $faker);
                
                $frekuensi = $this->weightedRandom([
                    '1-2x' => 65,
                    '3-5x' => 30,
                    '>5x' => 5
                ], $faker);
                
                $perhatian = $faker->numberBetween(1, 5);  // Rendah: 1-5
                $penghayatan = $faker->numberBetween(1, 4); // Rendah: 1-4
                break;

            case 'sedang':
                $durasi = $this->weightedRandom([
                    '<=1 jam' => 20,
                    '1-3 jam' => 50,
                    '3-5 jam' => 25,
                    '>5 jam' => 5
                ], $faker);
                
                $frekuensi = $this->weightedRandom([
                    '1-2x' => 25,
                    '3-5x' => 55,
                    '>5x' => 20
                ], $faker);
                
                $perhatian = $faker->numberBetween(4, 7);  // Sedang: 4-7
                $penghayatan = $faker->numberBetween(3, 6); // Sedang: 3-6
                break;

            case 'tinggi':
                $durasi = $this->weightedRandom([
                    '<=1 jam' => 5,
                    '1-3 jam' => 15,
                    '3-5 jam' => 40,
                    '>5 jam' => 40
                ], $faker);
                
                $frekuensi = $this->weightedRandom([
                    '1-2x' => 5,
                    '3-5x' => 25,
                    '>5x' => 70
                ], $faker);
                
                $perhatian = $faker->numberBetween(6, 10); // Tinggi: 6-10
                $penghayatan = $faker->numberBetween(6, 10); // Tinggi: 6-10
                break;
        }

        // Add some noise/variation untuk data yang lebih realistic (10% kemungkinan)
        if ($faker->boolean(10)) {
            $perhatian = $faker->numberBetween(1, 10);
            $penghayatan = $faker->numberBetween(1, 10);
        }

        // Create dataset/kuesioner
        Dataset::create([
            'respondent_id' => $responden->id,
            'durasi_penggunaan' => $durasi,
            'frekuensi_akses' => $frekuensi,
            'perhatian_konten' => $perhatian,
            'penghayatan' => $penghayatan,
            'label_intensitas' => $intensitas,
            'is_training_data' => true  // Set semua sebagai training data
        ]);
    }

    /**
     * Weighted random selection
     */
    private function weightedRandom(array $weights, $faker)
    {
        $totalWeight = array_sum($weights);
        $randomNum = $faker->numberBetween(1, $totalWeight);
        
        $weightSum = 0;
        foreach ($weights as $item => $weight) {
            $weightSum += $weight;
            if ($randomNum <= $weightSum) {
                return $item;
            }
        }
        
        return array_key_first($weights); // Fallback
    }
}