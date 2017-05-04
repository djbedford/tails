<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Input;

class StoresController extends Controller
{
    public function index(Request $request)
    {
        $stores = file_get_contents(resource_path('assets/stores.json'));
        $stores = json_decode($stores);

        $newStores = [];

        foreach ($stores as $store) {
            $latlon = json_decode($this->getLatLon($store->postcode));
            if (!empty($latlon)) {
                $lat = $latlon->result->latitude;
                $lon = $latlon->result->longitude;
                $map = $this->getStaticMap($lat, $lon);

                $newStores[] = [
                    'name' => str_replace('_', ' ', $store->name),
                    'postcode' => $store->postcode,
                    'map' => $map
                ];
            }
        }

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $col = new Collection($newStores);
        $perPage = 10;
        $currentPageSearchResults = $col->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $newStores = new LengthAwarePaginator($currentPageSearchResults, count($col), $perPage);

        return view('welcome')->withStores($newStores);
    }

    private function getLatLon($postcode)
    {
        try {
            $latlon = file_get_contents('https://api.postcodes.io/postcodes/' . str_replace(' ', '', $postcode));
            return $latlon;
        } catch (\Exception $err) {
            return '';
        }
    }

    private function getStaticMap($lat, $lon)
    {
        $key = 'AIzaSyDon4-e-bpiSpcNYQX7UmgQlHikkyWa2qE';
        $link = 'https://maps.googleapis.com/maps/api/staticmap?center=' . $lat . ',' .$lon . '&zoom=15&size=600x300&markers=color:green%7Clabel:A%7C' . $lat . ',' .$lon . '&key=' . $key;

        return $link;
    }
}
