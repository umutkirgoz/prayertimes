<?php
namespace UmutKirgoz\PrayerTimes\Repositories;

use Illuminate\Support\Collection;

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
     * Returns all countries
     * @return Collection
     */
    public function getCountries()
    {
        return $this->data->where('type', 'country');
    }

    /**
     * Returns the country
     * @param string $countrySlug
     * @return \stdClass
     */
    public function getCountry($countrySlug)
    {
        return $this->data->where('type', 'country')->where('slug', $countrySlug)->first();
    }

    /**
     * Returns the cities of given country
     * @param \stdClass $country
     * @param string $citySlug
     * @return Collection
     */
    public function getCities($country, $citySlug = null)
    {
        $cities = $this->data->where('type', 'city');
        return (null === $citySlug) ? $cities->where('parent_id', $country->id) : $cities->where('slug', $citySlug);
    }

    /**
     * Returns the towns of given cities
     * @param Collection $cities
     * @param string|null $townSlug
     * @return Collection
     */
    public function getTowns($cities, $townSlug = null)
    {
        $cityIds = $cities->pluck('id')->toArray();
        $towns = $this->data->where('type', 'town');

        if (null !== $townSlug) {
            return $towns->where('slug', $townSlug);
        }

        return $towns->filter(function ($item) use ($cityIds) {
            if (in_array($item->parent_id, $cityIds)) {
                return $item;
            }
        });
    }
}
