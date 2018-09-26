<?php
namespace UmutKirgoz\PrayerTimes\Repositories;

class LocationsRepository
{
    private $data;

    public function __construct()
    {
        $locationsDb = dirname(__FILE__)  . '/../Db/locations.json';

        $data = json_decode(file_get_contents($locationsDb));

        $this->data = collect($data);
    }

    public function get($countrySlug, $citySlug = null, $townSlug = null)
    {

        $country = $this->getCountry($countrySlug);

        $cities = $this->getCities($country, $citySlug);

        $towns = $this->getTowns($cities, $townSlug);


        return $towns;
    }

    private function getCountry($countrySlug)
    {
        return $this->data->where('type', 'country')->where('slug', $countrySlug)->first();
    }

    private function getCities($country, $citySlug)
    {
        $cities = $this->data->where('type', 'city');
        return (null === $citySlug) ? $cities->where('parent_id', $country->id) : $cities->where('slug', $citySlug);
    }

    private function getTowns($cities, $townSlug)
    {
        $cityIds = $cities->pluck('id')->toArray();
        $towns = $this->data->where('type', 'town');
        return (null === $townSlug) ? $towns->whereIn('parent_id', $cityIds) : $towns->where('slug', $townSlug);
    }
}
