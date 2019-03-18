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

        foreach (range(1,1000) as $index) {

    		$user = DB::table('users')->inRandomOrder()->first();
        	$spk = DB::table('spk')->inRandomOrder()->first();
        	$randDate = date('Y-m-d H:i:s', strtotime('+'.rand(0, 356 * 4).'  Days -3 Years'));
            $type = ['PROJECT', 'OFFICE', 'PAYMENT'];

            $rand_type = rand(0,2);

	        DB::table('pr')->insert([
	            'spk_id'         => ($rand_type == 0 ? $spk->id : null),
	            'user_id'        => $user->id,
                'type'           => $type[$rand_type],
	            'no_pr'          => $this->getPr(),
	            'name'           => $faker->sentence,
	            'datetime_order' => $randDate,
	            'division_id'    => $user->division_id,
	            'barcode'        => $faker->ean13,
	            'created_at'     => $datetime,
	            'updated_at'     => $datetime,
	        ]);

	        foreach (range(1,rand(1,10)) as $index) {

                $purchasing = DB::table('users')->where(function ($query) { 
                    $query->whereIn('position_id', getConfigValue('purchasing_position', true))
                        ->orWhereIn('id', getConfigValue('purchasing_user', true));
                    })
                    ->inRandomOrder()
                    ->first();

                $rand_confirm = rand(0,1);
                $status = ['WAITING', 'REJECTED', 'CONFIRMED', 'REVISION'];
                $status_purchasing = ['NONE', 'PENDING', 'STOCK', 'CANCEL'];

                $rand_status = $status[rand(0,1)];

                if($rand_confirm == 1)
                {
                    $rand_status = $status[rand(2,3)];
                }

                $deadline         = date('Y-m-d H:i:s', strtotime($randDate . ' +'.rand(0,100).' Days'));
                $datetime_confirm = date('Y-m-d H:i:s', strtotime($randDate . ' +'.rand(0,100).' Days'));

		        DB::table('pr_details')->insert([
		            'pr_id'             => DB::table('pr')->orderBy('id', 'DESC')->first()->id,
			        'item'              => $faker->sentence,
                    'quantity'          => rand(1, 50),
                    'unit'              => 'Pcs',
                    'deadline'          => $deadline,
                    'purchasing_id'     => $purchasing->id,
                    'status'            => $rand_status,
                    'status_purchasing' => $status_purchasing[rand(0,3)],
                    'service'           => rand(0,1),
                    'datetime_confirm'  => $rand_confirm == 1 ? $datetime_confirm : null,
			        'value'             => $rand_type != 0 ? rand(1, 99) * pow(10, rand(4,6)) : null,
		            'created_at'        => $datetime,
		            'updated_at'        => $datetime,
		        ]);

                if(rand(0,1) == 1 && $rand_confirm == 1)
                {
                    $type = ['TYPE 1', 'TYPE 2', 'TYPE 3', 'TYPE 4'];
                    $status_received = ['PROCESSING', 'CONFIRMED', 'COMPLAINT'];

                    $rand_status_received = rand(0,2);

                    $datetime_po = date('Y-m-d H:i:s', strtotime($datetime_confirm . ' +'.rand(0,100).' Days'));

                    $name_supplier = $faker->name;

                    foreach (range(1,rand(1,5)) as $index) {
                        DB::table('po')->insert([
                            'pr_detail_id'      => DB::table('pr_details')->orderBy('id', 'DESC')->first()->id,
                            'quantity'          => rand(1, 50),
                            'no_po'             => $faker->ean8,
                            'datetime_po'       => $datetime_po,
                            'type'              => $type[rand(0,3)],
                            'bank'              => $faker->word,
                            'name_supplier'     => $name_supplier,
                            'no_rekening'       => $faker->ean8,
                            'name_rekening'     => $name_supplier,
                            'value'             => rand(1, 99) * pow(10, rand(4,6)),
                            'status_received'   => $status_received[$rand_status_received],
                            'datetime_received' => $rand_status_received > 0 ? date('Y-m-d H:i:s', strtotime($datetime_po . ' +'.rand(0,100).' Days')) : null,
                            'note_received'     => $faker->sentence,
                            'created_at'        => $datetime,
                            'updated_at'        => $datetime,
                        ]);
                    }
                }
                
			}
		}
    }


    public function getPr()
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
