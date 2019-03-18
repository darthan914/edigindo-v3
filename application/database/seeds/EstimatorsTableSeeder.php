<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Faker\Factory as Faker;

class EstimatorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('id_ID');
        $datetime = date('Y-m-d H:i:s');

        foreach (range(1,1000) as $index) {

    		$sales = App\User::where(function ($query) { 
        		$query->whereIn('position_id', getConfigValue('sales_position', true))
        			->orWhereIn('id', getConfigValue('sales_user', true));
        		})
        		->inRandomOrder()
        		->first();

        	$estimator = App\User::where(function ($query) { 
        		$query->whereIn('position_id', getConfigValue('estimator_position', true))
        			->orWhereIn('id', getConfigValue('estimator_user', true));
        		})
        		->inRandomOrder()
        		->first();

	        DB::table('estimators')->insert([
	            'no_estimator'      => $this->getEstimator($sales),
	            'user_estimator_id' => $estimator->id,
	            'sales_id'          => $sales->id,
	            'name'              => $faker->sentence,
	            'description'       => $faker->paragraph,
	            'created_at'        => $datetime,
	            'updated_at'        => $datetime,
	        ]);

	        foreach (range(1,rand(3,5)) as $index) {

		        DB::table('estimator_details')->insert([
		            'estimator_id' => App\Models\Estimator::orderBy('id', 'DESC')->first()->id,
			        'item'         => $faker->sentence,
			        'value'        => rand(1, 10) * pow(10, rand(3,6)),
			        'note'         => $faker->paragraph,
		            'created_at'   => $datetime,
		            'updated_at'   => $datetime,
		        ]);
			}
		}
    }

    public function getEstimator($sales)
    {
        $estimator = App\Models\Estimator::select('no_estimator')
            ->where('no_estimator', 'like', str_pad($sales->no_ae, 2, '0', STR_PAD_LEFT) . "/%")
            ->orderBy('no_estimator', 'desc');

        $count = $estimator->count();
        $number = $estimator->first();

        if ($count == 0) {
            $numberEstimator = 0;
        } else {
            $numberEstimator = intval(substr($number->no_estimator, -5, 5));
        }

        return str_pad($sales->no_ae, 2, '0', STR_PAD_LEFT) . "/" . str_pad($numberEstimator + 1, 5, '0', STR_PAD_LEFT);
    }
}
