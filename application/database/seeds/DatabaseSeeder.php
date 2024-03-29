<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(CompaniesTableSeeder::class);
        $this->call(SpkTableSeeder::class);
        $this->call(OffersTableSeeder::class);
        $this->call(InvoicesTableSeeder::class);
        $this->call(PrTableSeeder::class);
        $this->call(SuppliersTableSeeder::class);
    }
}
