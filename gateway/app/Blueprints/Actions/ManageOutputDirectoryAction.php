<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ManageOutputDirectoryAction extends Action implements ActionContractInterface
{
    /**
     * @return mixed|void
     * @throws Exception
     */
    public function handle()
    {

        $functions = [
            'date' => Carbon::class,
        ];
        $path = $this->input->output_directory->folder;
        preg_match_all('/{(.*?)}/', $this->input->output_directory->folder, $matches);

        $variables = [];
        $fn = [];
        /**
         * collect data from string
         */
        collect(optional($matches)[1])->each(function ($match) use (&$variables, &$fn) {
            if (str_starts_with($match, '$$')) {
                $variables[] = Str::after($match, '$$');
            } elseif (str_starts_with($match, 'fn')) {
                $fn[] = Str::after($match, 'fn.');
            }
        });
        /**
         * replace based on variable
         */
        collect($variables)->each(function ($variable) use (&$path) {
            $model = explode('.', str_replace(['{', '}'], ['', ''], $this->input->args->{$variable}->mapper->value));
            $replace = array_change_key_case(array_flip((array)$this->input->args->{$variable}->matches),CASE_LOWER);
            $replace = $replace[Str::lower($this->{$model[0]}->{$model[1]})];
            $path = Str::replace("{\$\${$variable}}", $replace, $path);
        });
        /**
         * replace based on function
         */
        collect($fn)->each(function ($f) use ($functions, &$path) {
            $arr = explode(',', $f);
            $method = explode(':', $arr[2]);
            $path = Str::replace("{fn.{$f}}", Str::replace(' ', '_', $functions[$arr[0]]::{$arr[1]}()->{$method[0]}($method[1])), $path);
        });


        /**
         * tmp path
         */
        if (!Storage::disk('local')->exists($this->output_path . '/' . $path)) {
            Storage::disk('local')->makeDirectory($this->output_path . '/' . $path);
        }

        $this->request->merge(['override_path' => $path]);
    }
}
