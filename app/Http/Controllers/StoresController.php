<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;

class StoresController extends Controller
{
    private $storesList;

    public function __construct()
    {
        $this->storesList = $this->buildData();
    }

    private function buildData()
    {
        $stores = json_decode(file_get_contents(resource_path('assets/stores.json')));
        $storesList = [];

        foreach ($stores as $store) {
            $store_details = json_decode($this->getStoreDetails($store->postcode));

            if (!is_null($store_details)) {
                $storesList[] = [
                    'name' => str_replace('_', ' ', $store->name),
                    'postcode' => $store->postcode,
                    'outcode' => $store_details->result->outcode,
                    'latitude' => $store_details->result->latitude,
                    'longitude' => $store_details->result->longitude,
                    'map' => $this->getStaticMap($store_details->result->latitude, $store_details->result->longitude)
                ];
            }
        }

        return $storesList;
    }

    private function getStoreDetails($postcode)
    {
        try {
            $details = file_get_contents('https://api.postcodes.io/postcodes/' . str_replace(' ', '', $postcode));

            return $details;
        } catch (\Exception $err) {
            return null;
        }
    }

    public function index(Request $request)
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $col = new Collection($this->storesList);
        $perPage = 10;
        $currentPageSearchResults = $col->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $stores = new LengthAwarePaginator($currentPageSearchResults, count($col), $perPage);

        return view('welcome')->withStores($stores);
    }

    public function searchNearestStore(Request $request)
    {
        $store = json_decode($this->getStoreDetails($request->postcode));
        $nearestStores = $this->getNearestStores($store->result->latitude, $store->result->longitude);
        $newStores = [];

        if (!is_null($nearestStores)) {
            foreach ($nearestStores as $nearestStore) {
                foreach ($this->storesList as $store) {
                    if ($nearestStore->outcode === $store['outcode']) {
                        Log::info('Nearest Store Outcode : ' . $nearestStore->outcode);
                        Log::info('Store Outcode : ' . $store['outcode']);
                        $newStores[] = [
                            'name' => $store['name'],
                            'postcode' => $store['postcode'],
                            'distance' => number_format(($nearestStore->distance / 1000), 2),
                            'map' => $store['map']
                        ];
                    }
                }
            }

            usort($newStores, function ($a, $b) {
               return $a['distance'] - $b['distance'];
            });

            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $col = new Collection($newStores);
            $perPage = 10;
            $currentPageSearchResults = $col->slice(($currentPage - 1) * $perPage, $perPage)->all();
            $stores = new LengthAwarePaginator($currentPageSearchResults, count($col), $perPage);

            return view('welcome')->withStores($stores);
        }

        return view('welcome');
    }

    private function getStaticMap($lat, $lon)
    {
        $key = 'AIzaSyDon4-e-bpiSpcNYQX7UmgQlHikkyWa2qE';
        $link = 'https://maps.googleapis.com/maps/api/staticmap?center=' . $lat . ',' .$lon . '&zoom=15&size=600x300&markers=color:green%7Clabel:A%7C' . $lat . ',' .$lon . '&key=' . $key;

        return $link;
    }

    private function getNearestStores($lat, $lon)
    {
        try {
            $stores = json_decode(file_get_contents('https://api.postcodes.io/outcodes?lon=' . $lon . '&lat=' . $lat . '&radius=25000'));

            return $stores->result;
        } catch (\Exception $err) {
            return null;
        }
    }
}
