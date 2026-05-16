<?php

namespace App\Services;

class DeliveryFeeService
{
    /**
     * Base delivery fee in currency units.
     */
    private const BASE_FEE = 500;

    /**
     * Per-kilometer rate for max fee calculation.
     */
    private const PER_KM_RATE = 100;

    /**
     * Maximum delivery fee cap.
     */
    private const MAX_FEE_CAP = 2000;

    /**
     * Platform center coordinates (used as reference point for distance calculation).
     */
    private const CENTER_LAT = 33.5138;
    private const CENTER_LNG = 36.2765;

    /**
     * Calculate delivery fee range based on customer coordinates.
     *
     * @return array{min: float, max: float}
     */
    public function calculateFeeRange(float $lat, float $lng): array
    {
        $distanceKm = $this->calculateDistance($lat, $lng);

        $min = self::BASE_FEE;
        $max = self::BASE_FEE + ($distanceKm * self::PER_KM_RATE);

        // Cap the max fee
        $max = min($max, self::MAX_FEE_CAP);

        // Ensure max is at least min
        $max = max($max, $min);

        return [
            'min' => round($min, 2),
            'max' => round($max, 2),
        ];
    }

    /**
     * Calculate distance from platform center using Haversine formula.
     */
    private function calculateDistance(float $lat, float $lng): float
    {
        $earthRadius = 6371; // km

        $latDiff = deg2rad($lat - self::CENTER_LAT);
        $lngDiff = deg2rad($lng - self::CENTER_LNG);

        $a = sin($latDiff / 2) * sin($latDiff / 2)
            + cos(deg2rad(self::CENTER_LAT)) * cos(deg2rad($lat))
            * sin($lngDiff / 2) * sin($lngDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
