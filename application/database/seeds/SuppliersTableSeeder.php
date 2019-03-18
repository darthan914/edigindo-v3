<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Faker\Factory as Faker;

class SuppliersTableSeeder extends Seeder
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

        foreach (range(1,500) as $index)
        {
            $name = $faker->name;
            
        	DB::table('suppliers')->insert([
	            'name'           => $name,
	            'bank'           => $faker->word,
	            'name_rekening'  => $name,
	            'no_rekening'    => $faker->isbn10,
	            'contact_person' => phone_number_format($faker->e164PhoneNumber),
	            'home'           => phone_number_format($faker->e164PhoneNumber),
	            'phone'          => phone_number_format($faker->e164PhoneNumber),
	            'created_at'     => $datetime,
	            'updated_at'     => $datetime,
	        ]);
        }
    }
}
