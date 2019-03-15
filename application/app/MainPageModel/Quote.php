<?php

namespace App\MainPageModel;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $connection = 'mysql-mainpage';
    protected $table = 'quote';
}
