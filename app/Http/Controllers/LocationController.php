<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClosingTimesResource;
use App\Http\Resources\LocationResource;
use App\Location;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    private $client;

    public function __construct() {
        $this->client = new Client();
    }
    public function index() {
        return Location::all();
    }
    public function GetNearestLocation(Request $request) {
        //validate request input
        $rules = [
          'postcode' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())  {
            return response()->json([
                'response' => $validator->getMessageBag()->toArray()
            ], 422);
        }
        $postcode = $request->input('postcode');

        //check if the postcode is valid
        if(!$this->isValidPostcode($postcode)) {
            return response()->json([
                'response' => 'Invalid postcode'
            ], 422);
        }
        //get user coordinates
        $userAddressDetails = json_decode($this->client->get('api.postcodes.io/postcodes/' . $postcode)->getBody());
        $longitude = $userAddressDetails->result->longitude;
        $latitude = $userAddressDetails->result->latitude;

        //retrieve all drop locations
        $locations = Location::all();

        //for storing the drop details
        $nearestDrop = [];
        foreach ($locations as $location) {

            //get drop coordinates
            $dropAddressDetails = json_decode($this->client->get('api.postcodes.io/postcodes/' . $location->postcode)->getBody());
            $dropLongitude = $dropAddressDetails->result->longitude;
            $dropLatitude = $dropAddressDetails->result->latitude;

            //calculate the distance between the user and the drop
            $distance = $this->getDistance($latitude, $longitude, $dropLatitude, $dropLongitude);

            //check if the distance is lower than the current lowest and overwrite if so
            if(empty($nearestDrop) or $nearestDrop['distance'] > $distance) {
                $nearestDrop = [
                    'id' => $location->id,
                    'distance' => $distance,
                ];
            }
        }
        return new LocationResource(Location::find($nearestDrop['id']));
    }

    public function CreateNewLocation(Request $request) {
        //might also want to add unique to the postcode rule, as it's unlikely that there would be two drops at the same location
        $rules = [
            'postcode' => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())  {
            return response()->json([
                'response' => $validator->getMessageBag()->toArray()
            ], 422);
        }
        //check if the postcode is valid
        if(!$this->isValidPostcode($request->input('postcode'))) {
            return response()->json([
                'response' => 'Invalid postcode'
            ], 422);
        }

        $opening_times = $request->input('opening_times');
        $closing_times = $request->input('closing_times');
        $location = new Location;

        $location->postcode = $request->input('postcode');
        $location->open_Monday = array_key_exists ( 'monday' ,  $opening_times) ? $opening_times['monday'] : '00:00';
        $location->open_Tuesday = array_key_exists ( 'tuesday' ,  $opening_times) ? $opening_times['tuesday'] : '00:00';
        $location->open_Wednesday = array_key_exists ( 'wednesday' ,  $opening_times) ? $opening_times['wednesday'] : '00:00';
        $location->open_Thursday = array_key_exists ( 'thursday' ,  $opening_times) ? $opening_times['thursday'] : '00:00';
        $location->open_Friday = array_key_exists ( 'friday' ,  $opening_times) ? $opening_times['friday'] : '00:00';
        $location->open_Saturday = array_key_exists ( 'saturday' ,  $opening_times) ? $opening_times['saturday'] : '00:00';
        $location->open_Sunday = array_key_exists ( 'sunday' ,  $opening_times) ? $opening_times['sunday'] : '00:00';

        $location->closed_Monday = array_key_exists ( 'monday' ,  $closing_times) ? $closing_times['monday'] : '00:00';
        $location->closed_Tuesday = array_key_exists ( 'tuesday' ,  $closing_times) ? $closing_times['tuesday'] : '00:00';
        $location->closed_Wednesday = array_key_exists ( 'wednesday' ,  $closing_times) ? $closing_times['wednesday'] : '00:00';
        $location->closed_Thursday = array_key_exists ( 'thursday' ,  $closing_times) ? $closing_times['thursday'] : '00:00';
        $location->closed_Friday = array_key_exists ( 'friday' ,  $closing_times) ? $closing_times['friday'] : '00:00';
        $location->closed_Saturday = array_key_exists ( 'saturday' ,  $closing_times) ? $closing_times['saturday'] : '00:00';
        $location->closed_Sunday = array_key_exists ( 'sunday' ,  $closing_times) ? $closing_times['sunday'] : '00:00';

        $location->save();

        return response()->json([
            'location' => $location
        ]);
    }

    public function CalculateCashback(Request $request) {
        $ristretto = $request->input('Ristretto') or 0;
        $espresso  = $request->input('Espresso') or 0;
        $lungo = $request->input('Lungo') or 0;

        $totalCapsules = 0;
        $totalCapsules += $ristretto + $espresso + $lungo;
        //check if quantity is zero and return message
        if($totalCapsules === 0){
            return response()->json(['message' => 'Invalid quantity'], 422);
        }

        $cashback = 0;
        //perform calculation depending on the quantity
        switch ($totalCapsules) {
            case 0 < $totalCapsules && $totalCapsules <= 50:
                $cashback = $ristretto * 2 + $espresso * 4 + $lungo * 6;
                break;
            case 50 < $totalCapsules && $totalCapsules <= 500:
                $cashback = $ristretto * 3 + $espresso * 6 + $lungo * 9;
                break;
            case $totalCapsules > 500:
                $cashback = $ristretto * 5 + $espresso * 10 + $lungo * 15;
                break;
        }
        //convert to pounds
        $cashbackInPounds = $cashback/100;
        return response()->json(['cashback' => $cashbackInPounds]);
    }

    private function isValidPostcode($postcode) {
        $isValidPostcode = $this->client->get('api.postcodes.io/postcodes/' . $postcode . '/validate');
        return json_decode($isValidPostcode->getBody())->result == true;
    }

    //default earth radius in miles
    private function getDistance ($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 3963) {

        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

}
