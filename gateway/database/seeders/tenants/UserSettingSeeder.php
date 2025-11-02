<?php

namespace Database\Seeders\Tenants;

use App\Models\Tenants\User;
use Illuminate\Database\Seeder;

class UserSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        User::all()->each(
            fn($user) => collect($this->getData())->map(
                function($setting) use ($user) {
                    if($color = $user->settings()->where('key', 'theme_colors')->where('value', '#ffffff')->first()) {
                        $color->update(['value' => '#b04a4a']);
                    }
                    $user->settings()->firstOrCreate(['key' => $setting['key'], 'user_id' => $user->id], $setting);
                }
            )
        );
    }

    private function getData(): array
    {
        return [
            # Core - Colors - Theme Colors
            [
                'name' => 'The default user theme colors',
                'key' => 'theme_colors',
                "secure_variable" => false,
                'data_type' => 'objects',
                'data_variable' => 'hex:#ffffff,textColor:black||hex:#ccd7ff,textColor:black||hex:#99afff,textColor:black||hex:#6687ff,textColor:black||hex:#335fff,textColor:white||hex:#0037ff,textColor:white||hex:#002ccc,textColor:white||hex:#002199,textColor:white||hex:#001666,textColor:white||hex:#000b33,textColor:white',
                'multi_select' => false,
                'incremental' => null,
                'namespace' => 'themes',
                'area' => 'colors',
                'lexicon' => null,
                'value' => '#3095b4',
                "ctx_id" => 1,
            ],

            # Core - Language - Default Locale
            [
                "name" => "Default Locale",
                "key" => "manager_language",
                "secure_variable" => false,
                "data_type" => "array",
                "data_variable" => "EN||NL||AR||ES||DE",
                "multi_select" => false,
                "incremental" => null,
                "namespace" => "core",
                "area" => "language",
                "lexicon" => null,
                "value" => "EN",
                "ctx_id" => 1,
            ],

            # Core - Notifications - Do Not Disturb
            [
                "name" => "Do Not Disturb",
                "key" => "do_not_disturb",
                "secure_variable" => false,
                "data_type" => "boolean",
                "data_variable" => null,
                "multi_select" => false,
                "incremental" => null,
                "namespace" => "core",
                "area" => "Notification",
                "lexicon" => null,
                "value" => 0,
                "ctx_id" => 1,
            ],

            # Core - Notifications - Emergency Contact Email
            [
                "name" => "Emergency Contact Email",
                "key" => "emergency_contact_email",
                "secure_variable" => false,
                "data_type" => "string",
                "data_variable" => null,
                "multi_select" => false,
                "incremental" => null,
                "namespace" => "core",
                "area" => "Notification",
                "lexicon" => null,
                "ctx_id" => 1,
            ],
        ];
    }
}
