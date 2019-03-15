<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Faker\Factory as Faker;

class CompaniesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$faker = Faker::create('id_ID');
        $gender = ['male', 'female'];
        $datetime = date('Y-m-d H:i:s');

        foreach (range(1,200) as $index) {

    		$name = $faker->company;

	        DB::table('companies')->insert([
	            'name'       => strtoupper($name),
	            'short_name' => substr(strtoupper($name), 0, 5),
	            'phone'      => phone_number_format($faker->e164PhoneNumber),
	            'fax'        => phone_number_format($faker->e164PhoneNumber),
	            'created_at'  => $datetime,
	            'updated_at'  => $datetime,
	        ]);

	        foreach (range(1,rand(1,5)) as $index) {

	    		$rand_gender = $gender[rand(0, 1)];

		        DB::table('pic')->insert([
		            'company_id'  => App\Models\Company::orderBy('id', 'DESC')->first()->id,
			        'first_name'  => $faker->firstName($rand_gender),
	                'last_name'   => $faker->lastName, 
			        'gender'      => ($rand_gender == 'male' ? 'M' : 'F'),
			        'position'    => $faker->jobTitle,
			        'phone'       => phone_number_format($faker->e164PhoneNumber),
			        'email'       => $faker->unique()->safeEmail,
		            'created_at'  => $datetime,
		            'updated_at'  => $datetime,
		        ]);
			}

			foreach (range(1,rand(1,5)) as $index) {

		        DB::table('brands')->insert([
		            'company_id'  => App\Models\Company::orderBy('id', 'DESC')->first()->id,
			        'name'        => $faker->word,
		            'created_at'  => $datetime,
		            'updated_at'  => $datetime,
		        ]);
			}

			foreach (range(1,rand(1,5)) as $index) {

		        DB::table('addresses')->insert([
		            'company_id'  => App\Models\Company::orderBy('id', 'DESC')->first()->id,
			        'address'     => $faker->address,
		            'created_at'  => $datetime,
		            'updated_at'  => $datetime,
		        ]);
			}
		}

		
    }
}
