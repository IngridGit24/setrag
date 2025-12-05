<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seat;
use App\Models\Station;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $query = Trip::with(['originStation', 'destinationStation']);

        // Filtres
        if ($request->has('origin_station_id') && $request->origin_station_id) {
            $query->where('origin_station_id', $request->origin_station_id);
        }

        if ($request->has('destination_station_id') && $request->destination_station_id) {
            $query->where('destination_station_id', $request->destination_station_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('departure_time', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('departure_time', '<=', $request->date_to);
        }

        $trips = $query->orderBy('departure_time', 'desc')->paginate(20);
        $stations = Station::orderBy('name')->get();

        return view('admin.trips.index', compact('trips', 'stations'));
    }

    public function create()
    {
        $stations = Station::orderBy('name')->get();
        return view('admin.trips.create', compact('stations'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin_station_id' => 'required|exists:stations,id',
            'destination_station_id' => 'required|exists:stations,id|different:origin_station_id',
            'departure_time' => 'required|date|after_or_equal:today',
            'arrival_time' => 'required|date|after:departure_time',
            'seat_count' => 'required|integer|min:1|max:500',
            'price_second_class' => 'nullable|numeric|min:0',
            'price_first_class' => 'nullable|numeric|min:0',
            'price_vip' => 'nullable|numeric|min:45000|max:100000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $trip = Trip::create([
            'origin_station_id' => $request->origin_station_id,
            'destination_station_id' => $request->destination_station_id,
            'departure_time' => $request->departure_time,
            'arrival_time' => $request->arrival_time,
            'price_second_class' => $request->price_second_class ? (float) $request->price_second_class : null,
            'price_first_class' => $request->price_first_class ? (float) $request->price_first_class : null,
            'price_vip' => $request->price_vip ? (float) $request->price_vip : null,
        ]);

        // Créer les sièges avec répartition par classe
        $seatCount = (int) $request->seat_count;
        $secondClassCount = (int)($seatCount * 0.6);
        $firstClassCount = (int)($seatCount * 0.3);
        $vipCount = $seatCount - $secondClassCount - $firstClassCount;
        
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

        return redirect()->route('admin.trips.index')
            ->with('success', 'Voyage créé avec succès avec ' . $seatCount . ' places.');
    }

    public function show(Trip $trip)
    {
        $trip->load(['originStation', 'destinationStation', 'seats', 'bookings']);
        
        $availableSeats = $trip->seats()->where('status', 'AVAILABLE')->count();
        $soldSeats = $trip->seats()->where('status', 'SOLD')->count();
        $heldSeats = $trip->seats()->where('status', 'HELD')->count();
        $totalSeats = $trip->seats()->count();

        return view('admin.trips.show', compact('trip', 'availableSeats', 'soldSeats', 'heldSeats', 'totalSeats'));
    }

    public function edit(Trip $trip)
    {
        $stations = Station::orderBy('name')->get();
        $seatCount = $trip->seats()->count();
        
        return view('admin.trips.edit', compact('trip', 'stations', 'seatCount'));
    }

    public function update(Request $request, Trip $trip)
    {
        $validator = Validator::make($request->all(), [
            'origin_station_id' => 'required|exists:stations,id',
            'destination_station_id' => 'required|exists:stations,id|different:origin_station_id',
            'departure_time' => 'required|date',
            'arrival_time' => 'required|date|after:departure_time',
            'seat_count' => 'required|integer|min:1|max:500',
            'price_second_class' => 'nullable|numeric|min:0',
            'price_first_class' => 'nullable|numeric|min:0',
            'price_vip' => 'nullable|numeric|min:45000|max:100000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $trip->update([
            'origin_station_id' => $request->origin_station_id,
            'destination_station_id' => $request->destination_station_id,
            'departure_time' => $request->departure_time,
            'arrival_time' => $request->arrival_time,
            'price_second_class' => $request->price_second_class ? (float) $request->price_second_class : null,
            'price_first_class' => $request->price_first_class ? (float) $request->price_first_class : null,
            'price_vip' => $request->price_vip ? (float) $request->price_vip : null,
        ]);

        // Gérer les sièges
        $newSeatCount = (int) $request->seat_count;
        $currentSeatCount = $trip->seats()->count();

        if ($newSeatCount > $currentSeatCount) {
            // Ajouter des sièges avec répartition par classe
            $seatsToAdd = $newSeatCount - $currentSeatCount;
            $secondClassCount = (int)($seatsToAdd * 0.6);
            $firstClassCount = (int)($seatsToAdd * 0.3);
            $vipCount = $seatsToAdd - $secondClassCount - $firstClassCount;
            
            $seats = [];
            $seatNumber = $currentSeatCount + 1;
            
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
        } elseif ($newSeatCount < $currentSeatCount) {
            // Supprimer les sièges disponibles en excès
            $seatsToDelete = $trip->seats()
                ->where('status', 'AVAILABLE')
                ->orderBy('id', 'desc')
                ->limit($currentSeatCount - $newSeatCount)
                ->get();
            
            foreach ($seatsToDelete as $seat) {
                $seat->delete();
            }
        }

        return redirect()->route('admin.trips.index')
            ->with('success', 'Voyage mis à jour avec succès.');
    }

    public function destroy(Trip $trip)
    {
        // Vérifier s'il y a des réservations
        if ($trip->bookings()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer ce voyage car il contient des réservations.');
        }

        $trip->delete();

        return redirect()->route('admin.trips.index')
            ->with('success', 'Voyage supprimé avec succès.');
    }

    public function generateSeats(Trip $trip, Request $request)
    {
        $count = (int) ($request->input('count', 100));

        // Vérifier si des sièges existent déjà
        if ($trip->seats()->count() > 0) {
            return back()->with('error', 'Des sièges existent déjà pour ce voyage. Utilisez la modification pour changer le nombre de places.');
        }

        // Répartition par classe : 60% 2ème classe, 30% 1ère classe, 10% VIP
        $secondClassCount = (int)($count * 0.6);
        $firstClassCount = (int)($count * 0.3);
        $vipCount = $count - $secondClassCount - $firstClassCount;
        
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

        return back()->with('success', "{$count} places créées avec succès.");
    }
}

