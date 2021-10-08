<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;

    protected $table = 'scores';
    protected $fillable = ['id_user', 'credits', 'played_rolls', 'jackpot_hits'];
}
