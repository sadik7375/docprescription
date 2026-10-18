<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugAdvice extends Model
{
    use HasFactory;

    protected $table = 'drug_advices';
    protected $fillable = ['user_id', 'name', 'bangla_name'];
}
