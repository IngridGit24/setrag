<?php

namespace App\Services;

use App\Models\Station;
use App\Models\Trip;
use Carbon\Carbon;

class PricingService
{
    // Prix de base par route (2ème classe)
    private const ROUTE_PRICES = [
        'Libreville-Franceville' => 25000.0,
        'Franceville-Libreville' => 25000.0,
        'Libreville-Moanda' => 15000.0,
        'Moanda-Libreville' => 15000.0,
        'Libreville-Owendo' => 5000.0,
        'Owendo-Libreville' => 5000.0,
    ];

    // Multiplicateurs de classe
    private const CLASS_MULTIPLIERS = [
        'second_class' => 1.0,
        'first_class' => 1.5, // 50% plus cher que 2ème classe
        'VIP' => 2.0, // 100% plus cher que 2ème classe
    ];

    // Prix VIP (fixe selon la route)
    private const VIP_PRICES = [
        'Libreville-Franceville' => 75000.0, // Entre 45000 et 100000
        'Franceville-Libreville' => 75000.0,
        'Libreville-Moanda' => 60000.0,
        'Moanda-Libreville' => 60000.0,
        'Libreville-Owendo' => 50000.0,
        'Owendo-Libreville' => 50000.0,
    ];

    // Réductions par type de passager
    private const DISCOUNTS = [
        'adult' => 0.0,      // Pas de réduction
        'student' => 0.10,   // 10% de réduction
        'senior' => 0.30,    // 30% de réduction
        'child' => 1.0,      // 100% de réduction (gratuit)
    ];

    /**
     * Calculate the base price based on origin and destination stations and class
     * Uses trip-specific prices if available, otherwise falls back to default pricing
     */
    public function calculateBasePrice(int $originStationId, int $destinationStationId, string $class = 'second_class', ?Trip $trip = null): float
    {
        // Si le voyage a des prix définis, les utiliser en priorité
        if ($trip) {
            if ($class === 'VIP' && $trip->price_vip) {
                return (float) $trip->price_vip;
            }
            if ($class === 'first_class' && $trip->price_first_class) {
                return (float) $trip->price_first_class;
            }
            if ($class === 'second_class' && $trip->price_second_class) {
                return (float) $trip->price_second_class;
            }
        }

        // Sinon, utiliser les prix par défaut
        $origin = Station::find($originStationId);
        $destination = Station::find($destinationStationId);

        if (!$origin || !$destination) {
            return 10000.0; // Default fallback price
        }

        $routeKey = $origin->name . '-' . $destination->name;

        // Prix VIP (fixe selon la route)
        if ($class === 'VIP') {
            $vipPrice = self::VIP_PRICES[$routeKey] ?? 50000.0;
            // S'assurer que le prix VIP est entre 45000 et 100000
            return max(45000.0, min(100000.0, $vipPrice));
        }

        // Prix de base selon la route (2ème classe)
        $basePrice = self::ROUTE_PRICES[$routeKey] ?? 10000.0;

        // Appliquer le multiplicateur de classe
        $multiplier = self::CLASS_MULTIPLIERS[$class] ?? 1.0;
        
        return $basePrice * $multiplier;
    }

    /**
     * Calculate commission (5% of base price)
     */
    public function calculateCommission(float $basePrice): float
    {
        return $basePrice * 0.05;
    }

    /**
     * Calculate discount based on passenger type
     */
    public function calculateDiscount(float $basePrice, string $passengerType, ?string $birthDate = null): float
    {
        // Vérifier si c'est un enfant de moins de 5 ans
        if ($passengerType === 'child' && $birthDate) {
            $age = Carbon::parse($birthDate)->age;
            if ($age < 5) {
                return $basePrice; // Gratuit
            }
        }

        $discountRate = self::DISCOUNTS[$passengerType] ?? 0.0;
        return $basePrice * $discountRate;
    }

    /**
     * Calculate total price with class, commission, and discounts
     */
    public function calculateTotalPrice(
        float $basePrice,
        string $class = 'second_class',
        string $passengerType = 'adult',
        ?string $birthDate = null,
        int $passengers = 1
    ): array {
        // Calculer la commission
        $commission = $this->calculateCommission($basePrice);
        
        // Calculer la réduction
        $discount = $this->calculateDiscount($basePrice, $passengerType, $birthDate);
        
        // Prix après réduction
        $priceAfterDiscount = $basePrice - $discount;
        
        // Total avec commission
        $totalPrice = ($priceAfterDiscount + $commission) * $passengers;

        return [
            'base_price' => $basePrice * $passengers,
            'discount_amount' => $discount * $passengers,
            'commission' => $commission * $passengers,
            'total_price' => $totalPrice,
            'currency' => 'XAF',
            'class' => $class,
            'passenger_type' => $passengerType,
        ];
    }

    /**
     * Get price quote for a trip with class and passenger type
     */
    public function getQuote(
        Trip $trip,
        string $class = 'second_class',
        string $passengerType = 'adult',
        ?string $birthDate = null,
        int $passengers = 1
    ): array {
        $basePrice = $this->calculateBasePrice(
            $trip->origin_station_id,
            $trip->destination_station_id,
            $class,
            $trip // Passer le voyage pour utiliser les prix définis
        );

        return $this->calculateTotalPrice($basePrice, $class, $passengerType, $birthDate, $passengers);
    }
}

