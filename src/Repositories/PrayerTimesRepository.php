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
        $this->httpClient = new Client([
            'headers'   =>  [
                'User-Agent'    =>  $this->getRandomUserAgent()
            ]
        ]);
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
            throw new \Exception('PrayerTimes fetch failed', 500);
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

    private function getRandomUserAgent()
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:63.0) Gecko/20100101 Firefox/63.0',
            'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/44.0.2403.155 Safari/537.36',
            'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; en-US) AppleWebKit/534.7 (KHTML, like Gecko) Flock/3.5.3.4628 Chrome/7.0.517.450 Safari/534.7',
            'Mozilla/5.0 (Linux; U; Android 4.0.3; ko-kr; LG-L160L Build/IML74K) AppleWebkit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',
            'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 7.0; InfoPath.3; .NET CLR 3.1.40767; Trident/6.0; en-IN)',
        ];

        return $userAgents[array_rand($userAgents)];
    }
}
