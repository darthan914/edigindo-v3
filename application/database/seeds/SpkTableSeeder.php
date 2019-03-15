<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Faker\Factory as Faker;

class SpkTableSeeder extends Seeder
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

        	$randDate = date('Y-m-d', strtotime('+'.rand(0, 356 * 4).'  Days -3 Years'));
        	$sales = App\User::where(function ($query) { 
        		$query->whereIn('position_id', getConfigValue('sales_position', true))
        			->orWhereIn('id', getConfigValue('sales_user', true));
        		})
        		->inRandomOrder()
        		->first();

        	$company = App\Models\Company::inRandomOrder()->first();

        	DB::table('spk')->insert([

        		'sales_id'         => $sales->id,
		        'name'             => $faker->sentence,     
		        'no_spk'           => $this->getSpk($sales->id, $randDate),  
		        'main_division_id' => $sales->division_id,
		        'date_spk'         => $randDate,
		        'company_id'       => $company->id,
		        'brand_id'         => App\Models\Brand::where('company_id', $company->id)->inRandomOrder()->first()->id,
		        'pic_id'           => App\Models\Pic::where('company_id', $company->id)->inRandomOrder()->first()->id,
		        'address'          => App\Models\Address::where('company_id', $company->id)->inRandomOrder()->first()->address,
		        'ppn'              => rand(0,1) * 10,
		        'do_transaction'   => 0,
		        'note'             => $faker->paragraph,
		        'created_at'       => $datetime,
		        'updated_at'       => $datetime,  
        	]);

        	foreach (range(1,rand(2,20)) as $index) {


        		$source = ['INSOURCE', 'OUTSOURCE'];

        		DB::table('productions')->insert([
        			'spk_id'       => App\Models\Spk::orderBy('id', 'DESC')->first()->id,
			        'name'         => $faker->sentence,     
			        'division_id'  => $sales->division_id,
			        'source'       => $source[rand(0,1)],
			        'deadline'     => date('Y-m-d H:i:s', strtotime($randDate . ' +'.rand(1,4).' Weeks')),
			        'quantity'     => rand(1,10),
			        'hm'           => $hm = rand(1, 10) * pow(10, rand(3,6)),
			        'hj'           => $hm * (1 + (rand(25, 100) / 100)),
			        'free'         => 0,
                    'profitable'   => 1,
			        'detail'       => $faker->paragraph,
			        'created_at'   => $datetime,
			        'updated_at'   => $datetime, 
        		]);

        	}
        }
    }


    public function getSpk($sales, $date)
    {
        $user = App\User::where('id', $sales)->first();

        $spk = App\Models\Spk::select('no_spk')
            ->where('no_spk', 'like', str_pad(($user->no_ae == 0 ? $user->id : $user->no_ae), 2, '0', STR_PAD_LEFT) . "/" . date('y', strtotime($date)) . "-%")
            ->orderBy('no_spk', 'desc');

        $count = $spk->count();
        $year  = $spk->first();

        if ($count == 0) {
            $numberSpk = 0;
        } else {
            $numberSpk = intval(substr($year->no_spk, -3, 3));
        }

        return str_pad($user->no_ae, 2, '0', STR_PAD_LEFT) . "/" . date('y', strtotime($date)) . "-" . str_pad($numberSpk + 1, 3, '0', STR_PAD_LEFT);
    }
}
