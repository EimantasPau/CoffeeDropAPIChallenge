<?php

use App\Location;
use Illuminate\Database\Seeder;

class LocationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (($handle = fopen ( storage_path() . '/app/data.csv', 'r' )) !== false) {
            while (($data = fgetcsv ($handle, 1000, ',' )) !== false ) {
                $location = new Location();
                $location->postcode = $data[0];
                $location->open_Monday = $data[1];
                $location->open_Tuesday = $data[2];
                $location->open_Wednesday = $data[3];
                $location->open_Thursday = $data[4];
                $location->open_Friday = $data[5];
                $location->open_Saturday = $data[6];
                $location->open_Sunday = $data[7];
                $location->closed_Monday = $data[8];
                $location->closed_Tuesday = $data[9];
                $location->closed_Wednesday = $data[10];
                $location->closed_Thursday = $data[11];
                $location->closed_Friday = $data[12];
                $location->closed_Saturday = $data[13];
                $location->closed_Sunday = $data[14];

                $location->save();
            }
            fclose($handle);
        }
    }
}
