<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Respondent;
use App\Models\Dataset;
use Faker\Factory as Faker;

class BatchKuesionerSeeder extends Seeder
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

        $this->command->info('🚀 Mulai generate 3 batch kuesioner (300 data total)...');

        // BATCH 1: 100 data dengan prediksi RENDAH
        $this->command->info('📊 Batch 1: Generating 100 data intensitas RENDAH...');
        $this->generateBatch('rendah', 100, $faker, $jurusan);

        // BATCH 2: 100 data dengan prediksi SEDANG  
        $this->command->info('📊 Batch 2: Generating 100 data intensitas SEDANG...');
        $this->generateBatch('sedang', 100, $faker, $jurusan);

        // BATCH 3: 100 data dengan prediksi TINGGI
        $this->command->info('📊 Batch 3: Generating 100 data intensitas TINGGI...');
        $this->generateBatch('tinggi', 100, $faker, $jurusan);

        $this->command->info('✅ Selesai! Total 300 responden dengan 3 batch kuesioner:');
        $this->command->info('   - Batch 1 (Rendah): 100 data');
        $this->command->info('   - Batch 2 (Sedang): 100 data');
        $this->command->info('   - Batch 3 (Tinggi): 100 data');
        
        // Show distribution summary
        $this->showDistributionSummary();
    }

    /**
     * Generate batch data untuk intensitas tertentu
     */
    private function generateBatch(string $targetIntensitas, int $count, $faker, array $jurusan)
    {
        for ($i = 1; $i <= $count; $i++) {
            // Create responden
            $responden = Respondent::create([
                'nama' => $faker->name,
                'nim' => $this->generateNIM($faker),
                'jurusan' => $faker->randomElement($jurusan),
                'semester' => $faker->numberBetween(1, 8)
            ]);

            // Generate kuesioner sesuai target intensitas
            $this->generateTargetedKuesioner($responden, $targetIntensitas, $faker);
            
            // Progress indicator
            if ($i % 20 == 0) {
                $this->command->info("   Progress: {$i}/{$count} completed");
            }
        }
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
     * Generate kuesioner yang diarahkan ke target intensitas tertentu
     */
    private function generateTargetedKuesioner(Respondent $responden, string $targetIntensitas, $faker)
    {
        switch ($targetIntensitas) {
            case 'rendah':
                $data = $this->generateRendahProfile($faker);
                break;
            case 'sedang':
                $data = $this->generateSedangProfile($faker);
                break;
            case 'tinggi':
                $data = $this->generateTinggiProfile($faker);
                break;
            default:
                throw new \Exception("Unknown target intensitas: {$targetIntensitas}");
        }

        // Create dataset dengan 4 atribut utama untuk C4.5
        Dataset::create([
            'respondent_id' => $responden->id,
            
            // 4 atribut utama untuk C4.5
            'durasi_penggunaan' => $data['durasi_penggunaan'],
            'frekuensi_akses' => $data['frekuensi_akses'],
            'perhatian_konten' => $data['perhatian_konten'],
            'penghayatan' => $data['penghayatan'],
            
            // Label target
            'label_intensitas' => $targetIntensitas,
            'is_training_data' => true
        ]);
    }

    /**
     * Generate profil untuk intensitas RENDAH
     */
    private function generateRendahProfile($faker): array
    {
        return [
            // Atribut C4.5 - Karakteristik rendah
            'durasi_penggunaan' => $this->weightedRandom([
                '<=1 jam' => 70,
                '1-3 jam' => 25,
                '3-5 jam' => 5,
                '>5 jam' => 0
            ], $faker),
            
            'frekuensi_akses' => $this->weightedRandom([
                '1-2x' => 80,
                '3-5x' => 18,
                '>5x' => 2
            ], $faker),
            
            'perhatian_konten' => $faker->numberBetween(1, 3), // Rendah: 1-3
            'penghayatan' => $faker->numberBetween(1, 2),     // Rendah: 1-2
        ];
    }

    /**
     * Generate profil untuk intensitas SEDANG
     */
    private function generateSedangProfile($faker): array
    {
        return [
            // Atribut C4.5 - Karakteristik sedang
            'durasi_penggunaan' => $this->weightedRandom([
                '<=1 jam' => 20,
                '1-3 jam' => 50,
                '3-5 jam' => 25,
                '>5 jam' => 5
            ], $faker),
            
            'frekuensi_akses' => $this->weightedRandom([
                '1-2x' => 25,
                '3-5x' => 60,
                '>5x' => 15
            ], $faker),
            
            'perhatian_konten' => $faker->numberBetween(3, 4), // Sedang: 3-4
            'penghayatan' => $faker->numberBetween(3, 4),     // Sedang: 3-4
        ];
    }

    /**
     * Generate profil untuk intensitas TINGGI
     */
    private function generateTinggiProfile($faker): array
    {
        return [
            // Atribut C4.5 - Karakteristik tinggi
            'durasi_penggunaan' => $this->weightedRandom([
                '<=1 jam' => 5,
                '1-3 jam' => 20,
                '3-5 jam' => 40,
                '>5 jam' => 35
            ], $faker),
            
            'frekuensi_akses' => $this->weightedRandom([
                '1-2x' => 10,
                '3-5x' => 30,
                '>5x' => 60
            ], $faker),
            
            'perhatian_konten' => $faker->numberBetween(4, 5), // Tinggi: 4-5
            'penghayatan' => $faker->numberBetween(4, 5),     // Tinggi: 4-5
        ];
    }

    /**
     * Weighted random selection
     */
    private function weightedRandom(array $weights, $faker)
    {
        $rand = $faker->numberBetween(1, array_sum($weights));
        
        foreach ($weights as $option => $weight) {
            $rand -= $weight;
            if ($rand <= 0) {
                return $option;
            }
        }
        
        return array_key_first($weights);
    }

    /**
     * Show distribution summary
     */
    private function showDistributionSummary()
    {
        $rendah = Dataset::where('label_intensitas', 'rendah')->count();
        $sedang = Dataset::where('label_intensitas', 'sedang')->count();
        $tinggi = Dataset::where('label_intensitas', 'tinggi')->count();
        $total = $rendah + $sedang + $tinggi;

        $this->command->info('');
        $this->command->info('📊 DISTRIBUSI DATA FINAL:');
        $this->command->info("   Total Responden: " . Respondent::count());
        $this->command->info("   Total Dataset: {$total}");
        $this->command->info("   - Rendah: {$rendah} (" . round(($rendah/$total)*100, 1) . "%)");
        $this->command->info("   - Sedang: {$sedang} (" . round(($sedang/$total)*100, 1) . "%)");
        $this->command->info("   - Tinggi: {$tinggi} (" . round(($tinggi/$total)*100, 1) . "%)");
        $this->command->info('');
        
        // Show sample data from each batch
        $this->showSampleData();
    }

    /**
     * Show sample data from each batch
     */
    private function showSampleData()
    {
        $this->command->info('🔍 SAMPLE DATA DARI SETIAP BATCH:');
        
        foreach (['rendah', 'sedang', 'tinggi'] as $intensitas) {
            $sample = Dataset::where('label_intensitas', $intensitas)->first();
            if ($sample) {
                $this->command->info("   {$intensitas} -> Durasi: {$sample->durasi_penggunaan}, Frekuensi: {$sample->frekuensi_akses}, Perhatian: {$sample->perhatian_konten}");
            }
        }
        
        $this->command->info('');
        $this->command->info('✅ 3 Batch kuesioner siap digunakan untuk training C4.5!');
    }
}