<?php


use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GeoDistanceTwigExtension extends AbstractExtension {

    public function getFunctions()
    {
        return array(
            new TwigFunction('geo_distance', array($this, 'distance'))
        );
    }

    /**
     * This proved fairly accurate.
     * Modified slightly
     * https://stackoverflow.com/questions/10053358/measuring-the-distance-between-two-coordinates-in-php
     */
    function distance($lat1, $lon1, $lat2, $lon2, $unit) {

        if(empty($lat1)){ return "";}
        if(empty($lon1)){ return "";}
        if(empty($lat2)){ return "";}
        if(empty($lon2)){ return "";}

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }
}

?>