<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Faker\Factory as Faker;

class UsersTableSeeder extends Seeder
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

    	foreach (range(1,100) as $index) {

    		$rand_gender = $gender[rand(0, 1)];
    		$position_id = App\Models\Position::whereNotIn('id', getConfigValue('super_admin_position', true))->where('active', 1)->inRandomOrder()->first()->id;
    		
	        DB::table('users')->insert([
	            'username'    => $faker->unique()->userName,
	            'email'       => $faker->unique()->safeEmail,
	            'password'    => bcrypt('secret'),
	            'position_id' => $position_id,
	            'division_id' => App\Models\Division::inRandomOrder()->first()->id ?? null,
	            'no_ae'       => $this->getNoAE($position_id),
	            'first_name'  => $faker->firstName($rand_gender),
                'last_name'   => $faker->lastName,
	            'phone'       => phone_number_format($faker->e164PhoneNumber),
	            'active'      => 1,
                'parent_id'   => 1,
	            'created_at'  => $datetime,
	            'updated_at'  => $datetime,
	        ]);

            $parent = App\User::orderBy('id', 'DESC')->first();

            if(rand(0,1) == 1)
            {
                foreach (range(1,rand(2, 5)) as $index) {
            
                    $rand_gender = $gender[rand(0, 1)];
                    $first_name = $faker->unique()->firstName($rand_gender);
                    $last_name = $faker->lastName;

                    DB::table('users')->insert([
                        'username'    => $faker->unique()->userName,
                        'email'       => $faker->unique()->safeEmail,
                        'password'    => bcrypt('secret'),
                        'position_id' => $parent->position_id,
                        'division_id' => $parent->division_id,
                        'no_ae'       => $this->getNoAE($parent->division_id),
                        'first_name'  => $faker->firstName($rand_gender),
                        'last_name'   => $faker->lastName,
                        'phone'       => phone_number_format($faker->e164PhoneNumber),
                        'active'      => rand(-1,1),
                        'parent_id'   => $parent->id,
                        'created_at'  => $datetime,
                        'updated_at'  => $datetime,
                    ]);

                    $sub_parent = App\User::orderBy('id', 'DESC')->first();

                    if(rand(0,1) == 1)
                    {
                        foreach (range(1, rand(1, 2)) as $index) {
                    
                            $rand_gender = $gender[rand(0, 1)];
                            $first_name = $faker->unique()->firstName($rand_gender);
                            $last_name = $faker->lastName;
                            

                            DB::table('users')->insert([
                                'username'    => strtolower($first_name.$last_name),
                                'email'       => strtolower($first_name.$last_name.'@mail.com'),
                                'password'    => bcrypt('secret'),
                                'position_id' => $sub_parent->position_id,
                                'division_id' => $sub_parent->division_id,
                                'no_ae'       => $this->getNoAE($sub_parent->division_id),
                                'first_name'  => $first_name,
                                'last_name'   => $last_name,
                                'phone'       => phone_number_format($faker->e164PhoneNumber),
                                'active'      => rand(-1,1),
                                'parent_id'   => $sub_parent->id,
                                'created_at'  => $datetime,
                                'updated_at'  => $datetime,
                            ]);
                        }
                    }

                }
            }

            
		}

		
    }

    public function getNoAE($position)
    {

        // return $sales_id;
        $noAeCollection = App\User::select(DB::raw('GROUP_CONCAT(DISTINCT `no_ae`) as list_no_ae'))
            ->where('position_id', $position)
            ->where('active', 1)
            ->first();

        $no_ae      = 1;
        $list_no_ae = explode(',', $noAeCollection->list_no_ae);

        foreach ($list_no_ae as $list) {
            if (in_array($no_ae, $list_no_ae)) {
                $no_ae++;
            } else {
                break;
            }
        }

        return $no_ae;
    }
}
