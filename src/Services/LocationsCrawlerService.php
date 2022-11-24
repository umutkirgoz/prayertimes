<?php
namespace UmutKirgoz\PrayerTimes\Services;

use GuzzleHttp\Client;
use Stringy\StaticStringy;
use Symfony\Component\DomCrawler\Crawler;

class LocationsCrawlerService
{
    /**
     * @var Client Client
     */
    protected $httpClient;

    protected $locationsDb;

    public function __construct()
    {
        $this->locationsDb = dirname(__FILE__)  . '/../Db/locations.json';

        $this->httpClient = new Client([
            'headers'   =>  [
                'User-Agent'    =>  $this->getRandomUserAgent()
            ]
        ]);
    }

    /**
     * @throws \Exception
     */
    public function storeLocations()
    {
        $data = [];
        $countries = $this->getCountries();

        foreach ($countries as $country) {
            $data[] = $country;
            $cities = $this->getCities($country['id']);

            foreach ($cities as $city) {
                $hasTown = $city['has_towns'];
                unset($city['has_town']);
                $data[] = $city;
                if ($hasTown === false) {
                    continue;
                }
                $towns = $this->getTowns($country['id'], $city['id']);
                foreach ($towns as $town) {
                    $data[] = $town;
                }
            }
        }
        $json = json_encode($data);
        file_put_contents($this->locationsDb, $json);
    }

    public function crawlLocations()
    {
        $this->countries = $this->getCountries();
    }

    public function getCountries(): array
    {
        $url = "https://namazvakitleri.diyanet.gov.tr/tr-TR";

        $content = $this->get($url);

        $crawler = new Crawler($content);

        $xPath = '//select[contains(concat(" ",normalize-space(@class)," ")," country-select ")]//option';
        return $crawler->filterXPath($xPath)->each(function (Crawler $node) {
            $name = $node->text();
            $slug = StaticStringy::slugify($name);
            return [
                'id'    =>  $node->attr('value'),
                'parent_id' =>  "0",
                'type' =>  'country',
                'name'    =>  $name,
                'slug'    =>  $slug,
            ];
        });
    }

    /**
     * @throws \Exception
     */
    public function getCities($countryId)
    {
        $url = 'https://namazvakitleri.diyanet.gov.tr/tr-TR/home/GetRegList?ChangeType=country&CountryId=' .
            $countryId . '&Culture=tr-TR';

        $content = $this->get($url);

        $data = json_decode($content);

        $nameProp = 'IlceAdi';
        $idProp = 'IlceID';
        $type = 'city';
        $dataProp = 'StateRegionList';
        $hasTowns = false;
        if ($data->HasStateList === true) {
            $nameProp = 'SehirAdi';
            $idProp = 'SehirID';
            $dataProp = 'StateList';
            $hasTowns = true;
        }

        $resultSet = [];
        foreach ($data->$dataProp as $item) {
            $name = $item->$nameProp;
            $slug = StaticStringy::slugify($name);
            $resultSet[] = [
                'id'    =>  $item->$idProp,
                'parent_id'    =>  $countryId,
                'type' =>  $type,
                'name'    =>  $name,
                'slug'    =>  $slug,
                'has_towns' =>  $hasTowns,
            ];
        }
        return $resultSet;
    }

    public function getTowns($countryId, $cityId): array
    {
        $url = "https://namazvakitleri.diyanet.gov.tr/tr-TR/home/GetRegList?ChangeType=state&CountryId=".$countryId
            ."&Culture=tr-TR&StateId=" . $cityId;

        $content = $this->get($url);

        $data = json_decode($content);

        if (!isset($data->StateRegionList)) {
            throw new \Exception('Cannot access data');
        }

        $resultSet = [];
        foreach ($data->StateRegionList as $state) {
            $name = $state->IlceAdi;
            $slug = StaticStringy::slugify($name);
            $resultSet[] = [
                'id'    =>  $state->IlceID,
                'parent_id'    =>  $cityId,
                'type' =>  'town',
                'name'    =>  $name,
                'slug'    =>  $slug
            ];
        }
        return $resultSet;
    }

    /**
     * Returns the contents of given URL
     * @param string $url
     * @return string
     * @throws \Exception
     */
    public function get($url): string
    {
        try {
            $response = $this->httpClient->get($url);
            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            throw new \Exception('Crawl failed', 500);
        }
    }

    private function getRandomUserAgent(): string
    {
        return \Campo\UserAgent::random();
    }
}
