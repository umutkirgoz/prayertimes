<?php

namespace UmutKirgoz\PrayerTimes\Repositories;

use GuzzleHttp\Client;

/**
 * Class PrayerTimesRepository
 * @package UmutKirgoz\PrayerTimes\Repositories
 */
class PrayerTimesRepository
{
    const URL_PATTERN = 'http://namazvakitleri.diyanet.gov.tr/tr-TR/%s/%s-icin-namaz-vakti';

    /**
     * @var \GuzzleHttp\Client Client
     */
    protected $httpClient;

    /**
     * PrayerTimesRepository constructor.
     */
    public function __construct()
    {
        $this->httpClient = new Client();
    }

    /**
     * Returns the
     * @param $location
     * @return string
     */
    public function get($location)
    {
        $url = $this->buildUrl($location);

        try {
            $response = $this->httpClient->get($url);
            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @param $location
     * @return string
     */
    private function buildUrl($location)
    {
        return sprintf(self::URL_PATTERN, $location->id, $location->slug);
    }
}
