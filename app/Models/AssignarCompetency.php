<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignarCompetency extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignar_competency_id',
        'name',
        'exp_req',
        'comp_date_req',
        'issue_req',
        'licence_number_label',
        'state',
        'active',
    ];
}
