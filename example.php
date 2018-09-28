<?php

use UmutKirgoz\PrayerTimes\Repositories\LocationsRepository;
use UmutKirgoz\PrayerTimes\Services\PrayerTimesService;

include ('vendor/autoload.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Location Repository
 */
$locationRepository = new LocationsRepository();

/**
 * Returns all countries
 */

//$allCountries = $locationRepository->getCountries();

$prayerTimesService = new PrayerTimesService();

/**
 * Returns all prayer times of the country's each town
 * Caution : Huge data return, it can be useful to minimize the count
 */
//$prayerTimes = $prayerTimesService->get('turkiye');

/**
 * Returns all prayer times of the city's each town
 */
$prayerTimes = $prayerTimesService->get('turkiye', 'amasya');

/**
 * Return the prayer times of given town
 */
//$prayerTimes = $prayerTimesService->get('turkiye', 'amasya', 'merzifon');

dd($prayerTimes);