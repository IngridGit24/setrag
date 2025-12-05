<?php

namespace Database\Seeders;

use App\Models\Seat;
use App\Models\Trip;
use Illuminate\Database\Seeder;

class SeatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trips = Trip::doesntHave('seats')->get();
        
        if ($trips->isEmpty()) {
            $this->command->info('Tous les voyages ont déjà des sièges.');
            return;
        }

        $totalSeats = 0;
        $defaultSeatCount = 100; // Nombre de sièges par défaut
        // Répartition : 60% 2ème classe, 30% 1ère classe, 10% VIP
        $secondClassCount = (int)($defaultSeatCount * 0.6);
        $firstClassCount = (int)($defaultSeatCount * 0.3);
        $vipCount = $defaultSeatCount - $secondClassCount - $firstClassCount;

        foreach ($trips as $trip) {
            $seats = [];
            $seatNumber = 1;
            
            // Sièges 2ème classe
            for ($i = 0; $i < $secondClassCount; $i++) {
                $seats[] = [
                    'trip_id' => $trip->id,
                    'seat_no' => "{$seatNumber}A",
                    'class' => 'second_class',
                    'status' => 'AVAILABLE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $seatNumber++;
            }
            
            // Sièges 1ère classe
            for ($i = 0; $i < $firstClassCount; $i++) {
                $seats[] = [
                    'trip_id' => $trip->id,
                    'seat_no' => "{$seatNumber}B",
                    'class' => 'first_class',
                    'status' => 'AVAILABLE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $seatNumber++;
            }
            
            // Sièges VIP
            for ($i = 0; $i < $vipCount; $i++) {
                $seats[] = [
                    'trip_id' => $trip->id,
                    'seat_no' => "{$seatNumber}V",
                    'class' => 'VIP',
                    'status' => 'AVAILABLE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $seatNumber++;
            }
            
            Seat::insert($seats);
            $totalSeats += $defaultSeatCount;
        }

        $this->command->info("✅ {$totalSeats} sièges créés pour {$trips->count()} voyages !");
    }
}

