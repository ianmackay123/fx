<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Data extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'instrument_id',
        'bid',
        'ask',
        'price'

    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}
