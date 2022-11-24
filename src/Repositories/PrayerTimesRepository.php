<?php

namespace UmutKirgoz\PrayerTimes\Repositories;

use GuzzleHttp\Client;

/**
 * Class PrayerTimesRepository
 * @package UmutKirgoz\PrayerTimes\Repositories
 */
class PrayerTimesRepository
{
    const URL_PATTERN = 'https://namazvakitleri.diyanet.gov.tr/tr-TR/%s/%s-icin-namaz-vakti';

    /**
     * Returns the
     * @param $location
     * @return string
     */
    public function get($location): string
    {
        $httpClient = new Client([
            'headers'   =>  [
                'User-Agent'    =>  $this->getRandomUserAgent()
            ]
        ]);

        $url = $this->buildUrl($location);
        $url .= '?' . rand(10000, 99999);

        $response = $httpClient->get($url);
        return $response->getBody()->getContents();
    }

    /**
     * @param $location
     * @return string
     */
    private function buildUrl($location): string
    {
        return sprintf(self::URL_PATTERN, $location->id, $location->slug);
    }

    private function getRandomUserAgent(): string
    {
        return \Campo\UserAgent::random();
    }
}
