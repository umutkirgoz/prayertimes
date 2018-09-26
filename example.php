<?php

use UmutKirgoz\PrayerTimes\Services\PrayerTimesService;

include ('vendor/autoload.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);


$prayerTimesService = new PrayerTimesService();
$prayerTimes = $prayerTimesService->get('turkiye', 'amasya');




dd($prayerTimes);