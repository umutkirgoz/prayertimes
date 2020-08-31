<?php

use UmutKirgoz\PrayerTimes\Services\LocationsCrawlerService;

include ('vendor/autoload.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);


$crawlerService = new LocationsCrawlerService();

//dd($crawlerService->getCities(166));

$crawlerService->storeLocations();
exit;

//$countries = $crawlerService->getCountries();
//dd($countries);

//Almanya
//$cities = $crawlerService->getCities(13);
//dd($cities);

//Almanya / Bayern
$towns = $crawlerService->getTowns(13, 851);

dd($towns);