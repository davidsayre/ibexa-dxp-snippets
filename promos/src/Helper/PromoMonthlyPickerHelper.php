<?php

namespace App\Helper;

use DateTime;

class PromoMonthlyPickerHelper {
    /**
     * Given a specific date, pick from the pool of promos
     * Days of the month take effect is > 1 promo found
     * @param $promos
     * @param $date
     * @return mixed|null
     */
    public static function pickMonthlyPromoByDate($promos, $date = null)
    {

        if (!is_a($date, DateTime::class)) {
            $date = new DateTime();
        }

        // Monthly Promos
        $dayNumber = $date->format('d');
        $numPromos = count($promos);

        $lastDom = new DateTime('last day of this month');
        $numDaysOfCurrentMonth = $lastDom->format('d');

        // single vs multiple calculations by day of month
        if (is_array($promos) && !empty($promos)) {
            if (count($promos) == 1) {
                return $promos[0];
            } else {
                $pos = floor(($dayNumber / $numDaysOfCurrentMonth) * $numPromos); // zero based array
                // exact day edge case
                if ($pos > count($promos) - 1) {
                    $pos = $pos - 1;
                }
                return $promos[$pos];
            }
        }
        return null;
    }
}