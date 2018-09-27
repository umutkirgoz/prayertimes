<?php
namespace UmutKirgoz\PrayerTimes\Repositories;

use Tightenco\Collect\Support\Collection;

/**
 * Manages the locations
 * Class LocationsRepository
 * @package UmutKirgoz\PrayerTimes\Repositories
 */
class LocationsRepository
{
    /**
     *
     * @var Collection $data
     *
     */
    private $data;

    /**
     * LocationsRepository constructor.
     */
    public function __construct()
    {
        $locationsDb = dirname(__FILE__)  . '/../Db/locations.json';

        $data = json_decode(file_get_contents($locationsDb));

        $this->data = collect($data);
    }

    /**
     * @param string $countrySlug
     * @param string|null $citySlug
     * @param string|null $townSlug
     * @return Collection
     */
    public function get($countrySlug, $citySlug = null, $townSlug = null)
    {
        $country = $this->getCountry($countrySlug);

        $cities = $this->getCities($country, $citySlug);

        $towns = $this->getTowns($cities, $townSlug);

        return $towns;
    }

    /**
     * Returns the country
     * @param string $countrySlug
     * @return \stdClass
     */
    private function getCountry($countrySlug)
    {
        return $this->data->where('type', 'country')->where('slug', $countrySlug)->first();
    }

    /**
     * @param \stdClass $country
     * @param string $citySlug
     * @return Collection
     */
    private function getCities($country, $citySlug)
    {
        $cities = $this->data->where('type', 'city');
        return (null === $citySlug) ? $cities->where('parent_id', $country->id) : $cities->where('slug', $citySlug);
    }

    /**
     * @param Collection$cities
     * @param $townSlug
     * @return static
     */
    private function getTowns($cities, $townSlug)
    {
        $cityIds = $cities->pluck('id')->toArray();
        $towns = $this->data->where('type', 'town');
        return (null === $townSlug) ? $towns->whereIn('parent_id', $cityIds) : $towns->where('slug', $townSlug);
    }
}
