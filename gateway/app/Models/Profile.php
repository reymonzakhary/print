<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Profile
 * @package App\Models
 */
class Profile extends Model
{


    protected $primaryKey = 'identifier'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'identifier', 'gender',
        'first_name', 'middle_name', 'last_name',
        'dob', 'avatar', 'bio', 'custom_filed'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'identifier'
    ];

    /**
     * timestamp set to dates
     * @var array
     */
    protected $dates = [
        'dob'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];


    /**
     * @return STRING
     * getting a avatar of the user
     */
    public function avatar()
    {
        return ($this->email) ? 'https://www.gravatar.com/avatar/' . md5($this->email) . '?s=45&d=mm' : '';
    }
}
