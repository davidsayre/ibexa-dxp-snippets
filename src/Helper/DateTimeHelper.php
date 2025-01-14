<?php

namespace App\Helper;

use DateTime;
use DateTimeZone;
use Exception;

class DateTimeHelper
{

    /**
     * Try and convert various date formats into \DateTime objects or return null if failure
     * Null is important for later logic
     * @param $dateString
     * @return DateTime|null
     */
    public static function convertDateString($dateString)
    {
        $dateString = trim($dateString); // trim spaces JIC

        // v3 CleanSWell 2024+
        // Try Javascript .toUTCString() .000Z nonstandard format : 2024-05-04T18:42:00.000Z
        // NOTE: The correct ISO format for UTC is: 2024-05-04T02:28:14+00:00 (+00:00 and not the '.000Z' stuff)
        try {
            if (stripos($dateString, 'Z') !== false) {
                // read in Zulu syntax
                $iso8601 = DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $dateString, new DateTimeZone('UTC'));
                if (is_object($iso8601)) {
                    //echo "ISO8602 date (match)" . $dateString . "\n";{
                    return $iso8601;
                }
            }
        } catch (Exception $e) {
            //echo "unable to create date from ZULU ".$e->getMessage()."\n";
        }

        // v3 CleanSWell 2024+
        // Try Javascript LOCAL time (not UTC) .toISO() with .000 milliseconds : 2004-02-12T15:19:21.123+00:00
        try {
            $isoTimezone = DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $dateString);
            if (is_object($isoTimezone)) {
                // MUST convert to UTC! MySQL does NOT autoconvert timezones
                $isoTimezone->setTimezone(new DateTimeZone('UTC'));
                //echo "ISO date (match)" . $dateString . "\n";
                return $isoTimezone;
            }
        } catch (Exception $e) {
            //echo "unable to create date from ISO ".$e->getMessage()."\n";
        }

        // try unix timestamp as Milliseconds (
        try {
            if (!empty($dateString) && is_numeric($dateString) && $dateString > 0 && strlen($dateString) > 12) {
                $convertToSeconds = (intval($dateString / 1000));
                //echo "Timestamp in milliseconds (UTC) " . $dateString . " >> " . $convertToSeconds . "\n";
                $tsMSDate = DateTime::createFromFormat('U', $convertToSeconds);
                if (is_object($tsMSDate)) {
                    //echo "Timestamp in MS (match)" . $dateString . "\n";
                    return $tsMSDate;
                }
            }
        } catch (Exception $e) {
            // echo "Unable to create date from timestamp in milliseconds ".$e->getMessage()."\n";
        }

        // try unix timestamp as seconds (
        try {
            if (!empty($dateString) && is_numeric($dateString) && strlen($dateString) > 9) {
                $tryTS = $dateString;
                //echo "Timestamp in seconds (UTC) " . $dateString . " >> ".$tryTS."\n";
                $tsSecDate = DateTime::createFromFormat('U', $tryTS);
                if (is_object($tsSecDate)) {
                    //echo "Timestamp (match)" . $dateString . "\n";
                    return $tsSecDate;
                }
            }
        } catch (Exception $e) {
            // echo "Unable to create date from timestamp ".$e->getMessage()."\n";
        }

        // v2 CleanSwell long date Y-m-d h:i:s
        // assume timezone assume UTC ???
        try {
            $shortDate = DateTime::createFromFormat("Y-m-d h:i:s", $dateString);
            if (is_object($shortDate)) {
                $shortDate->setTimezone(new DateTimeZone('UTC'));
                //echo "Short date (match) " . $dateString . "\n";  // assume timezone UTC
                return $shortDate;
            }
        } catch (Exception $e) {
            // echo "Unable to create date from trimed short date ".$e->getMessage()."\n";
        }

        // v2 CleanSwell short date Y-m-d
        // assume timezone assume UTC ???
        try {
            $shortDate = DateTime::createFromFormat("Y-m-d", $dateString);
            if (is_object($shortDate)) {
                $shortDate->setTimezone(new DateTimeZone('UTC'));
                //echo "Short date (match) " . $dateString . "\n";  // assume timezone UTC
                return $shortDate;
            }
        } catch (Exception $e) {
            // echo "Unable to create date from trimed short date ".$e->getMessage()."\n";
        }

        return null;
    }
}

?>