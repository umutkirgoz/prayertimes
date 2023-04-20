<?php

namespace UmutKirgoz\PrayerTimes\Services;

use Symfony\Component\DomCrawler\Crawler;
use UmutKirgoz\PrayerTimes\Repositories\LocationsRepository;
use UmutKirgoz\PrayerTimes\Repositories\PrayerTimesRepository;

/**
 *
 * Class PrayerTimesService
 * @package UmutKirgoz\PrayerTimes\Services
 */
class PrayerTimesService
{
    /**
     * @var LocationsRepository
     */
    private $locationRepository;

    /**
     * @var PrayerTimesRepository
     */
    private $prayerTimesRepository;

    /**
     * @var array
     */
    private $dataMap = [
        'date',
        'hicri',
        'imsak',
        'gunes',
        'ogle',
        'ikindi',
        'aksam',
        'yatsi'
    ];

    /**
     * @var array
     */
    private $monthMap = [
        'Ocak'      =>  '01',
        'Şubat'     =>  '02',
        'Mart'      =>  '03',
        'Nisan'     =>  '04',
        'Mayıs'     =>  '05',
        'Haziran'   =>  '06',
        'Temmuz'    =>  '07',
        'Ağustos'   =>  '08',
        'Eylül'     =>  '09',
        'Ekim'      =>  '10',
        'Kasım'     =>  '11',
        'Aralık'    =>  '12',
    ];

    /**
     *
     * PrayerTimesService constructor.
     */
    public function __construct()
    {
        $this->locationRepository = new LocationsRepository();

        $this->prayerTimesRepository = new PrayerTimesRepository();
    }

    /**
     * @param $countrySlug
     * @param string|null $citySlug
     * @param string|null $townSlug
     * @return array
     */
    public function get($countrySlug, string $citySlug = null, string $townSlug = null): array
    {
        $locations = $this->locationRepository->get($countrySlug, $citySlug, $townSlug);

        $result = [];
        foreach ($locations as $location) {
            $data = $this->getData($location);
            if (!empty($data)) {
                $result[$location->id] = [
                    'location'  =>  $location,
                    'data'      =>  $data
                ];
            }
        }

        return $result;
    }

    /**
     * @param $location
     * @return array
     * @throws \Exception
     */
    public function getData($location): array
    {
        $content = $this->prayerTimesRepository->get($location);

        $data = $this->parse($content);

        return $this->transformData($data);
    }

    /**
     * @param $content
     * @return array
     */
    private function parse($content): array
    {
        $crawler = new Crawler($content);

        return $crawler->filterXPath('//*[@id="tab-1"]/div/table/tbody/tr')->each(function (Crawler $node) {
            return $node->filterXPath('//*/td')->each(function (Crawler $node) {
                return $node->text();
            });
        });
    }

    /**
     * @param $data
     * @return array
     * @throws \Exception
     */
    private function transformData($data): array
    {
        $result = [];

        foreach ($data as $datum) {
            $dayData = [];
            for ($i = 1; $i < count($datum); $i++) {
                $dayData[$this->dataMap[$i]] = $datum[$i];
            }
            $dayDate = $this->getDate($datum);

            $result[$dayDate] = $dayData;
        }
        return $result;
    }

    /**
     * @param $data
     * @return string
     * @throws \Exception
     */
    private function getDate($data): string
    {
        try {
            $parts = explode(' ', $data[0]);
            $month = $this->monthMap[$parts[1]];
            return sprintf('%s-%s-%s', $parts[2], $month, $parts[0]);
        } catch (\Exception $e) {
            throw new \Exception('Invalid Date');
        }
    }
}
