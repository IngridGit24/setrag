<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Gares principales - Coordonnées basées sur la ligne Transgabonais
        $mainStations = [
            ['name' => 'Owendo', 'latitude' => 0.3000, 'longitude' => 9.5000], // Près de Libreville, départ ouest
            ['name' => 'Ndjolé', 'latitude' => 0.1833, 'longitude' => 10.6833], // Moyen-Ogooué
            ['name' => 'Lopé', 'latitude' => -0.1833, 'longitude' => 11.6000], // Ogooué-Ivindo, près du Parc National
            ['name' => 'Booué', 'latitude' => -0.1000, 'longitude' => 11.9333], // Ogooué-Ivindo
            ['name' => 'Lastourville', 'latitude' => -0.8167, 'longitude' => 12.7000], // Ogooué-Lolo
            ['name' => 'Moanda', 'latitude' => -1.5667, 'longitude' => 13.2000], // Haut-Ogooué, embarquement minerai
            ['name' => 'Franceville', 'latitude' => -1.6333, 'longitude' => 13.5833], // Haut-Ogooué, terminus est
        ];

        // Gares secondaires - Coordonnées approximatives le long de la ligne
        $secondaryStations = [
            ['name' => 'Abanga', 'latitude' => 0.2800, 'longitude' => 9.6500],
            ['name' => 'Alembé', 'latitude' => 0.2000, 'longitude' => 10.1000],
            ['name' => 'Andem', 'latitude' => 0.1500, 'longitude' => 10.4500],
            ['name' => 'Ayem', 'latitude' => 0.1000, 'longitude' => 10.8000],
            ['name' => 'Ivindo', 'latitude' => -0.0500, 'longitude' => 11.1500],
            ['name' => 'Offoué', 'latitude' => -0.2000, 'longitude' => 11.4500],
            ['name' => 'Otoumbi', 'latitude' => -0.3000, 'longitude' => 11.7500],
            ['name' => 'Oyan', 'latitude' => -0.4000, 'longitude' => 12.0500],
            ['name' => 'Bissouma', 'latitude' => -0.5000, 'longitude' => 12.2500],
            ['name' => 'Doumé', 'latitude' => -0.6000, 'longitude' => 12.4500],
            ['name' => 'Lifouta', 'latitude' => -0.7000, 'longitude' => 12.6000],
            ['name' => 'M\'Bel', 'latitude' => -0.7500, 'longitude' => 12.6500],
            ['name' => 'Mboungou-Mbadouma', 'latitude' => -0.7800, 'longitude' => 12.6800],
            ['name' => 'Milolé', 'latitude' => -0.8200, 'longitude' => 12.7200],
            ['name' => 'Mouyabi', 'latitude' => -1.2000, 'longitude' => 13.0000],
            ['name' => 'Ntoum', 'latitude' => 0.4000, 'longitude' => 9.6000], // Estuaire
        ];

        // Insérer les gares principales
        foreach ($mainStations as $station) {
            Station::firstOrCreate(
                ['name' => $station['name']],
                [
                    'latitude' => $station['latitude'],
                    'longitude' => $station['longitude'],
                ]
            );
        }

        // Insérer les gares secondaires
        foreach ($secondaryStations as $station) {
            Station::firstOrCreate(
                ['name' => $station['name']],
                [
                    'latitude' => $station['latitude'],
                    'longitude' => $station['longitude'],
                ]
            );
        }

        $totalStations = count($mainStations) + count($secondaryStations);
        $this->command->info("✅ {$totalStations} gares ajoutées avec succès !");
        $this->command->info("   - " . count($mainStations) . " gares principales");
        $this->command->info("   - " . count($secondaryStations) . " gares secondaires");
    }
}

