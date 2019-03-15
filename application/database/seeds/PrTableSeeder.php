<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Faker\Factory as Faker;

class PrTableSeeder extends Seeder
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

        foreach (range(1,5000) as $index) {

    		$user = App\User::inRandomOrder()->first();
        	$spk = DB::table('spk')->inRandomOrder()->first();
        	$randDate = date('Y-m-d H:i:s', strtotime('+'.rand(0, 356 * 4).'  Days -3 Years'));

	        DB::table('pr')->insert([
	            'spk_id'         => $this->getEstimator($sales),
	            'user_id'        => $estimator->id,
	            'sales_id'       => $sales->id,
	            'type'           => $faker->sentence,
	            'name'           => $faker->word,
	            'datetime_order' => $randDate,
	            'deadline'       => date('Y-m-d H:i:s', strtotime($randDate . ' +'.rand(1,4).' Weeks')),
	            'division_id'    => $$user->division_id,
	            'barcode'        => rand(10000000, 99999999),
	            'created_at'     => $datetime,
	            'updated_at'     => $datetime,
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


    public function getPr($sales)
    {
        $estimator = DB::table('pr')->select('no_pr')
            ->orderBy('no_pr', 'desc');

        $count = $estimator->count();
        $number = $estimator->first();

        if ($count == 0) {
            $numberPr = 0;
        } else {
            $numberPr = intval(substr($number->no_pr, -8, 8));
        }

        return str_pad($numberPr + 1, 8, '0', STR_PAD_LEFT);
    }
}
