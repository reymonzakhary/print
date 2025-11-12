<?php

namespace App\Models\Tenant;

use App\Models\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class Profile
 * @package App\Models
 */
class Profile extends Model
{
    use InteractsWithMedia;

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
        'dob' => 'datetime'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'custom_filed' => AsArrayObject::class
    ];


    /**
     * @return string
     * getting a avatar of the user
     */
    public function avatar()
    {
        return ($this->email) ? 'https://www.gravatar.com/avatar/' . md5($this->email) . '?s=45&d=mm' : '';
    }

    public function getAvatarAttribute()
    {
        return $this->getFirstMedia('avatar');
    }

    public function updateAvatar($avatar)
    {
        $this->addFirstMedia($avatar, collection:'avatar');
    }

    /**
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return Str::replace(
            '  ', ' ',
            Str::ucfirst($this->first_name) . ' ' .
            Str::ucfirst($this->middle_name) . ' ' .
            Str::ucfirst($this->last_name)
        );
    }

}
