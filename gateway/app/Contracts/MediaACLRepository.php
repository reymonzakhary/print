<?php


namespace App\Contracts;


use Alexusmai\LaravelFileManager\Services\ACLService\ACLRepository;
use App\Models\Tenants\Media\MediaSource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MediaACLRepository implements ACLRepository
{
    /**
     * Get user ID
     *
     * @return mixed
     */
    public function getUserID()
    {
        return Auth::id();
    }

    /**
     * Get ACL rules list for user
     *
     * @return array
     */
    public function getRules(): array
    {
        if ($this->getUserID() === 1) {
            return [
                ['disk' => 'tenancy', 'path' => '*', 'access' => 2],
            ];
        }
        $teams = DB::table('users')
            ->where('users.id', '=', $this->getUserID())
            ->join('user_teams as ut', 'users.id', '=', 'ut.user_id')
            ->join('teams as t', 'ut.team_id', '=', 't.id')
            ->join('accessables as a', function ($join) {
                $join->on('a.team_id', '=', 't.id')
                    ->where('a.accessable_type', '=', MediaSource::class);
            })
            ->join('media_source_rules as mr', function ($join) {
                $join->on('mr.media_source_id', '=', 'a.accessable_id');
            })
            ->get(['disk', 'path', 'access'])
            ->map(function ($item) {
                return get_object_vars($item);
            })
            ->all();

        $rules = DB::table('media_source_rules')
            ->where('media_source_rules.user_id', '=', $this->getUserID())
            ->get(['disk', 'path', 'access'])
            ->map(function ($item) {
                $item->path = ltrim(Str::replace(tenant()->uuid, '', $item->path), '/');
                return get_object_vars($item);
            })
            ->all();
        return array_merge($teams, $rules);
    }


}
