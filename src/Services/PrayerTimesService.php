<?php

namespace UmutKirgoz\PrayerTimes\Services;

use Symfony\Component\DomCrawler\Crawler;
use UmutKirgoz\PrayerTimes\Repositories\LocationsRepository;
use UmutKirgoz\PrayerTimes\Repositories\PrayerTimesRepository;

class PrayerTimesService
{
    private $locationRepository;

    private $prayerTimesRepository;

    private $data = [];

    private $dataMap = [
        'date',
        'fajr',
        'sun',
        'dhuhr',
        'asr',
        'maghrib',
        'isha'
    ];

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

    public function __construct()
    {
        $this->locationRepository = new LocationsRepository();

        $this->prayerTimesRepository = new PrayerTimesRepository();
    }

    public function get($countrySlug, $citySlug = null, $townSlug = null)
    {
        $locations = $this->locationRepository->get($countrySlug, $citySlug, $townSlug);

        foreach ($locations as $location) {
            $data = $this->getData($location);
            if (!empty($data)) {
                $this->data[$location->id] = [
                    'location'  =>  $location,
                    'data'      =>  $data
                ];
            }
        }

        return $this->data;
    }

    private function getData($location)
    {
        $content = $this->prayerTimesRepository->get($location);

        $datas = $this->parse($content);

        return $this->transformData($datas);
    }

    private function parse($content)
    {
        $crawler = new Crawler($content);

        $data = $crawler->filterXPath('//*[@id="tab-1"]/div/table/tbody/tr')->each(function (Crawler $node, $i) {
            return $node->filter('td')->each(function (Crawler $node, $i) {
                return $node->text();
            });
        });

        return $data;
    }

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
