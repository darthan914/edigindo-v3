<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Faker\Factory as Faker;

class OffersTableSeeder extends Seeder
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

        	$sales = DB::table('users')->where(function ($query) { 
        		$query->whereIn('position_id', getConfigValue('sales_position', true))
        			->orWhereIn('id', getConfigValue('sales_user', true));
        		})
        		->inRandomOrder()
        		->first();

        	$company = DB::table('companies')->inRandomOrder()->first();

        	DB::table('offers')->insert([

        		'no_document'    => $this->getDocument($randDate, $sales, $company),
		        'name'           => $faker->sentence,     
		        'date_offer'     => $randDate,
		        'company_id'     => $company->id,
		        'brand_id'       => DB::table('brands')->where('company_id', $company->id)->inRandomOrder()->first()->id,
		        'pic_id'         => DB::table('pic')->where('company_id', $company->id)->inRandomOrder()->first()->id,
		        'address'        => DB::table('addresses')->where('company_id', $company->id)->inRandomOrder()->first()->address,
		        'sales_id'       => $sales->id,
		        'division_id'    => $sales->division_id,
		        'ppn'            => rand(0,1) * 10,
		        'note'           => $faker->paragraph,
		        'total_price'    => in_array($sales->division_id, getConfigValue('division_expo', true)) ? rand(1, 99) * pow(10, rand(6,8)) : 0,
		        'created_at'     => $datetime,
		        'updated_at'     => $datetime,  
        	]);

        	if(in_array($sales->division_id, getConfigValue('division_expo', true)))
        	{
        		$status = ['WAITING', 'CANCEL', 'SUCCESS', 'FAILED'];
        		$rand_status = rand(0,3);
        		$reason = ['PRICING', 'TIMELINE', 'OTHER'];

        		DB::table('offer_details')->insert([
        			'offer_id'   => DB::table('offers')->orderBy('id', 'DESC')->first()->id,
        			'name'       => $faker->sentence,
        			'detail'     => $faker->paragraph,
        			'quantity'   => 1,
        			'value'      => 0,
        			'status'     => $status[$rand_status],
        			'reason'     => ($rand_status == 3 ? $reason[rand(0,2)] : null),
        			'created_at' => $datetime,
			        'updated_at' => $datetime, 
    			]);
        	}
        	else
        	{
        		foreach (range(1,rand(1,5)) as $index) {
        			$status = ['WAITING', 'CANCEL', 'SUCCESS', 'FAILED'];
	        		$rand_status = rand(0,3);
	        		$reason = ['PRICING', 'TIMELINE', 'OTHER'];

        			DB::table('offer_details')->insert([
	        			'offer_id'   => DB::table('offers')->orderBy('id', 'DESC')->first()->id,
	        			'name'       => $faker->sentence,
	        			'detail'     => $faker->paragraph,
	        			'quantity'   => rand(1,30),
	        			'unit'       => 'pcs',
	        			'value'      => rand(1, 99) * pow(10, rand(4,6)),
	        			'status'     => $status[$rand_status],
	        			'reason'     => ($rand_status == 3 ? $reason[rand(0,2)] : null),
	        			'created_at' => $datetime,
				        'updated_at' => $datetime, 
	    			]);
        		}
        	}
        }
    }

    public function getDocument($date, $sales, $company)
    {
        $countDoc = DB::table('offers')
            ->where('no_document', 'like', "___/PH/" . str_pad(($sales->no_ae == 0 ? $sales->id : $sales->no_ae), 3, '0', STR_PAD_LEFT) . "/" . strtoupper(date('M', strtotime($date))) . "/" . date('Y', strtotime($date)) . "/" . strtoupper($company->short_name))
            ->count();

        $noPenawaran = 1;
        if ($countDoc != 0) {
            $noPenawaran = $countDoc + 1;
        }

        $document = str_pad($noPenawaran, 3, '0', STR_PAD_LEFT) . "/PH/" . str_pad(($sales->no_ae == 0 ? $sales->id : $sales->no_ae), 3, '0', STR_PAD_LEFT) . "/" . strtoupper(date('M', strtotime($date))) . "/" . date('Y', strtotime($date)) . "/" . strtoupper($company->short_name);

        return $document;
    }
}
