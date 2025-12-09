<?php

namespace Database\Seeders;

use App\Models\Station;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les gares principales
        $owendo = Station::where('name', 'Owendo')->first();
        $franceville = Station::where('name', 'Franceville')->first();
        $moanda = Station::where('name', 'Moanda')->first();
        $ndjole = Station::where('name', 'Ndjolé')->first();
        $lope = Station::where('name', 'Lopé')->first();
        $booue = Station::where('name', 'Booué')->first();
        $lastourville = Station::where('name', 'Lastourville')->first();

        if (!$owendo || !$franceville) {
            $this->command->error('Les gares principales (Owendo, Franceville) doivent exister !');
            return;
        }

        // Dates de début et fin
        $startDate = Carbon::now();
        $endDate = Carbon::create(2024, 12, 31, 23, 59, 59);
        
        // Si on est déjà après le 31 décembre 2024, utiliser l'année suivante
        if ($startDate->year > 2024) {
            $endDate = Carbon::create($startDate->year, 12, 31, 23, 59, 59);
        }

        // Horaires de départ depuis Owendo (vers l'est)
        $morningDeparture = 7; // 7h00
        $afternoonDeparture = 14; // 14h00

        // Durées approximatives en heures
        $durations = [
            'Owendo-Franceville' => 14, // 14 heures
            'Owendo-Moanda' => 12,
            'Owendo-Ndjolé' => 2,
            'Owendo-Lopé' => 5,
            'Owendo-Booué' => 7,
            'Owendo-Lastourville' => 10,
        ];

        $tripsCreated = 0;
        $tripsSkipped = 0;
        $currentDate = $startDate->copy()->startOfDay();

        // Générer les voyages jusqu'au 31 décembre
        while ($currentDate->lte($endDate)) {
            // Voyage du matin : Owendo → Franceville
            $departureTime = $currentDate->copy()->setTime($morningDeparture, 0);
            $arrivalTime = $departureTime->copy()->addHours($durations['Owendo-Franceville']);

            $created = Trip::firstOrCreate(
                [
                    'origin_station_id' => $owendo->id,
                    'destination_station_id' => $franceville->id,
                    'departure_time' => $departureTime,
                ],
                [
                    'arrival_time' => $arrivalTime,
                ]
            );
            if ($created->wasRecentlyCreated) {
                $tripsCreated++;
            } else {
                $tripsSkipped++;
            }

            // Voyage de l'après-midi : Owendo → Franceville
            $departureTime = $currentDate->copy()->setTime($afternoonDeparture, 0);
            $arrivalTime = $departureTime->copy()->addHours($durations['Owendo-Franceville']);

            $created = Trip::firstOrCreate(
                [
                    'origin_station_id' => $owendo->id,
                    'destination_station_id' => $franceville->id,
                    'departure_time' => $departureTime,
                ],
                [
                    'arrival_time' => $arrivalTime,
                ]
            );
            if ($created->wasRecentlyCreated) {
                $tripsCreated++;
            } else {
                $tripsSkipped++;
            }

            // Voyage retour : Franceville → Owendo (départ le lendemain matin)
            $nextDay = $currentDate->copy()->addDay();
            if ($nextDay->lte($endDate)) {
                $departureTime = $nextDay->copy()->setTime($morningDeparture, 0);
                $arrivalTime = $departureTime->copy()->addHours($durations['Owendo-Franceville']);

                $created = Trip::firstOrCreate(
                    [
                        'origin_station_id' => $franceville->id,
                        'destination_station_id' => $owendo->id,
                        'departure_time' => $departureTime,
                    ],
                    [
                        'arrival_time' => $arrivalTime,
                    ]
                );
                if ($created->wasRecentlyCreated) {
                    $tripsCreated++;
                } else {
                    $tripsSkipped++;
                }
            }

            // Voyages vers Moanda (2 par jour)
            $departureTime = $currentDate->copy()->setTime($morningDeparture, 0);
            $arrivalTime = $departureTime->copy()->addHours($durations['Owendo-Moanda']);

            $created = Trip::firstOrCreate(
                [
                    'origin_station_id' => $owendo->id,
                    'destination_station_id' => $moanda->id,
                    'departure_time' => $departureTime,
                ],
                [
                    'arrival_time' => $arrivalTime,
                ]
            );
            if ($created->wasRecentlyCreated) {
                $tripsCreated++;
            } else {
                $tripsSkipped++;
            }

            $departureTime = $currentDate->copy()->setTime($afternoonDeparture, 0);
            $arrivalTime = $departureTime->copy()->addHours($durations['Owendo-Moanda']);

            $created = Trip::firstOrCreate(
                [
                    'origin_station_id' => $owendo->id,
                    'destination_station_id' => $moanda->id,
                    'departure_time' => $departureTime,
                ],
                [
                    'arrival_time' => $arrivalTime,
                ]
            );
            if ($created->wasRecentlyCreated) {
                $tripsCreated++;
            } else {
                $tripsSkipped++;
            }

            // Passer au jour suivant
            $currentDate->addDay();
        }

        $this->command->info("✅ {$tripsCreated} nouveaux voyages créés jusqu'au " . $endDate->format('d/m/Y') . " !");
        if ($tripsSkipped > 0) {
            $this->command->info("   ({$tripsSkipped} voyages existants ignorés)");
        }
    }
}

