<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ExtractDataFromOrderAction  extends Action implements ActionContractInterface
{

    public function handle()
    {
        $output = [];
        collect($this->input->map)->each(function($row, $k) use (&$output){
            $output[Str::lower($k)] = Str::startsWith( $row,'$')?
            $this->getVariable($row):$row;
        });

        $this->output['path'] = array_merge($output, ['path' => 'path']);
    }

    /**
     * @param $variable
     * @return string
     */
    private function getVariable($variable)
    {
        $auth = $this->request->user;
        switch ($variable) {
            case Str::startsWith($variable, '$auth'):
                $variable = Str::after($variable, '$auth->');
                return match ($variable) {
                    'last_name' =>  Str::ucfirst($auth->profile->last_name),
                    'first_name' =>  Str::ucfirst($auth->profile->first_name),
                    'middle_name' =>  Str::ucfirst($auth->profile->middle_name),
                    'full_name' => $auth->profile->fullName,
                    default => ''
                };
            break;
            case Str::startsWith($variable, '$category'):
                $variable = Str::after($variable, '$category->');
                return match ($variable) {
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                    default => ''
                };
            break;
            case Str::startsWith($variable, '$now()'):
                $variable = Str::after($variable, '$now()->');
                switch ($variable) {
                    case Str::startsWith($variable, 'format'):
                        preg_match('/\((.*?)\)/', $variable, $matches);
                            return Carbon::now()->format(optional($matches)[1]);
                        break;
                    default:
                        return '';
                }
            break;
            case Str::startsWith($variable, '$order'):
                $variable = Str::after($variable, '$order->');
                return $auth->orders()->where('created_at', '>', Carbon::now()->subMinutes(10))->latest()->first()?->{$variable};
                break;

            default:
                    return '';
        }
    }
}
