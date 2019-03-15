<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Faker\Factory as Faker;

class InvoicesTableSeeder extends Seeder
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

        $spk = DB::table('spk')->get();

        foreach ($spk as $list) {

        	$no_admin = rand(-3, getConfigValue('num_admin'));

        	DB::table('spk')->where('id', $list->id)->update(['code_admin' => $no_admin]);

        	if($no_admin > 0)
        	{
        		$rand_insert = rand(1, 5);

	        	if($rand_insert > 0)
	        	{
	        		foreach (range(1, $rand_insert) as $list2) {
	        			$datetime_add_complete = $no_invoice = $value_invoice =  $datetime_add_invoice = $date_faktur = $datetime_add_faktur = $date_received = $datetime_add_received = $no_sending = $address_sending = $datetime_add_sending = $note = null;

		        		if(rand(0,1) > 0)
		        		{
		        			$datetime_add_complete = $datetime;
		        			$no_invoice = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
		        			$value_invoice = rand(1, 99) * pow(10, rand(4,7));
		        		}

		        		if(isset($datetime_add_complete) && rand(0,1) > 0)
		        		{
		        			$datetime_add_invoice = $datetime;
		        		}

		        		if(isset($datetime_add_invoice) && rand(0,1) > 0)
		        		{
		        			$date_faktur = $datetime;
		        			$datetime_add_faktur = $datetime;
		        		}

		        		if(isset($date_faktur) && rand(0,1) > 0)
		        		{
		        			$date_received = $datetime;
		        			$datetime_add_received = $datetime;
		        		}

		        		if(isset($datetime_add_received) && rand(0,1) > 0)
		        		{
		        			$no_sending = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
		        			$address_sending = $faker->address;
		        			$datetime_add_sending = $datetime;
		        			$note = $faker->word;
		        		}
		        		
		        		DB::table('invoices')->insert([
				            'spk_id'                => $list->id,
					        'datetime_add_complete' => $datetime_add_complete,
					        'no_invoice'            => $no_invoice,
					        'value_invoice'         => $value_invoice,
					        'datetime_add_invoice'  => $datetime_add_invoice,
					        'date_faktur'           => $date_faktur,
					        'datetime_add_faktur'   => $datetime_add_faktur,
					        'date_received'         => $date_received,
					        'datetime_add_received' => $datetime_add_received,
					        'no_sending'            => $no_sending,
					        'address_sending'       => $address_sending,
					        'datetime_add_sending'  => $datetime_add_sending,
					        'note'                  => $note,
				            'created_at'            => $datetime,
				            'updated_at'            => $datetime
				        ]);
		        	}
	        	}
        	}
        	
        }
    }
}
