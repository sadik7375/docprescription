<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugDose extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'bangla_name'];
}
