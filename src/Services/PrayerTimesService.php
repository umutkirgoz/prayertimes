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
    public function get($countrySlug, $citySlug = null, $townSlug = null)
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
     */
    private function getData($location)
    {
        $content = $this->prayerTimesRepository->get($location);

        $datas = $this->parse($content);

        return $this->transformData($datas);
    }

    /**
     * @param $content
     * @return array
     */
    private function parse($content)
    {
        $crawler = new Crawler($content);

        $data = $crawler->filterXPath('//*[@id="tab-1"]/div/table/tbody/tr')->each(function (Crawler $node) {
            return $node->filterXPath('//*/td')->each(function (Crawler $node) {
                return $node->text();
            });
        });

        return $data;
    }

    /**
     * @param $datas
     * @return array
     */
    private function transformData($datas)
    {
        $result = [];

        foreach ($datas as $data) {
            $dayData = [];
            for ($i = 1; $i < count($data); $i++) {
                $dayData[$this->dataMap[$i]] = $data[$i];
            }
            $dayDate = $this->getDate($data);

            $result[$dayDate] = $dayData;
        }
        return $result;
    }

    /**
     * @param $data
     * @return string
     */
    private function getDate($data)
    {
        try {
            $parts = explode(' ', $data[0]);
            $month = $this->monthMap[$parts[1]];
            return sprintf('%s-%s-%s', $parts[2], $month, $parts[0]);
        } catch (\Exception $e) {
            dd('Invalid Date');
        }
    }
}
