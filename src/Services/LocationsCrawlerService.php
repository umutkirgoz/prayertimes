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
        $this->locationsDb = dirname(__FILE__)  . '/../Db/locations-new.json';

        $this->httpClient = new Client([
            'headers'   =>  [
                'User-Agent'    =>  $this->getRandomUserAgent()
            ]
        ]);
    }

    public function storeLocations()
    {
        $data = [];
        $countries = $this->getCountries();

        foreach ($countries as $country) {
            $data[] = $country;



            $cities = $this->getCities($country['id']);
            if ($country['id'] == 166) {
                dd($country, $cities);
            }
            foreach ($cities as $city) {
                $hasTown = $city['has_town'];
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

    public function getCountries()
    {
        $url = "https://namazvakitleri.diyanet.gov.tr/tr-TR";

        $content = $this->get($url);

        $crawler = new Crawler($content);

        return $crawler->filterXPath('//select[contains(concat(" ",normalize-space(@class)," ")," country-select ")]//option')->each(function (Crawler $node) {
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

    public function getCities($countryId)
    {
        $url = 'https://namazvakitleri.diyanet.gov.tr/tr-TR/home/GetRegList?ChangeType=country&CountryId='.$countryId.'&Culture=tr-TR';

        $content = $this->get($url);

        $data = json_decode($content);

        if (!isset($data->StateList)) {
            throw new \Exception('Cannot access data');
        }

        $resultSet = [];
        foreach ($data->StateList as $state) {
            $name = $state->SehirAdi;
            $slug = StaticStringy::slugify($name);
            $resultSet[] = [
                'id'    =>  $state->SehirID,
                'parent_id'    =>  $countryId,
                'type' =>  'city',
                'name'    =>  $name,
                'slug'    =>  $slug,
                'has_town' =>  (isset($data->HasStateList) && $data->HasStateList === true)
            ];
        }
        return $resultSet;
    }

    public function getTowns($countryId, $cityId)
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
     */
    public function get($url)
    {
        try {
            $response = $this->httpClient->get($url);
            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            throw new \Exception('Crawl failed', 500);
        }
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
