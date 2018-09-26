<?php
namespace UmutKirgoz\PrayerTimes\Repositories;

use GuzzleHttp\Client;

class PrayerTimesRepository
{
    const URL_PATTERN = 'http://namazvakitleri.diyanet.gov.tr/tr-TR/%s/%s-icin-namaz-vakti';

    protected $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client();
    }

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

    private function buildUrl($location)
    {
        return sprintf(self::URL_PATTERN, $location->id, $location->slug);
    }
}
