<?php

namespace Modules\Cms\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Cms\Entities\Variable;

class VariablesTableSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
//        Model::unguard();

        $templateVariables = [
            [

                "label" => "Small text",
                "key" => "text",
                "folder_id" => null,
                "data_type" => "string",
                "input_type" => "text",
                "default_value" => "Your text here",
                "placeholder" => "Your text here",
                "class" => null,
                "secure_variable" => false,
                "multi_select" => false,
                "incremental" => false,
                "min_count" => 0,
                "max_count" => 0,
                "min_size" => 0,
                "max_size" => 255
            ],
            [
                "label" => "Long text",
                "key" => "long_text",
                "folder_id" => null,
                "data_type" => "string",
                "input_type" => "textarea",
                "default_value" => null,
                "placeholder" => "Your long text here",
                "class" => null,
                "secure_variable" => false,
                "multi_select" => false,
                "incremental" => false,
                "min_count" => 0,
                "max_count" => 0,
                "min_size" => 0,
                "max_size" => 1000
            ],
            [
                "label" => "Rich Text",
                "key" => "rich_text",
                "folder_id" => null,
                "data_type" => "wysiwyg_editor",
                "input_type" => "textarea",
                "default_value" => null,
                "placeholder" => "Advanced text here",
                "class" => "advanced_text",
                "secure_variable" => false,
                "multi_select" => false,
                "incremental" => false,
                "min_count" => 0,
                "max_count" => 0,
                "min_size" => 0,
                "max_size" => 1000
            ],
            [
                "label" => "Tags",
                "key" => "tags",
                "folder_id" => null,
                "data_type" => "array",
                "input_type" => "textarea",
                "default_value" => null,
                "placeholder" => "Tags here",
                "class" => "tags",
                "secure_variable" => false,
                "multi_select" => false,
                "incremental" => false,
                "min_count" => 0,
                "max_count" => 0,
                "min_size" => 0,
                "max_size" => 1000
            ],
            [
                "label" => "File",
                "key" => "file",
                "folder_id" => null,
                "data_type" => "file",
                "input_type" => "file",
                "default_value" => null,
                "placeholder" => "File here",
                "class" => "file",
                "secure_variable" => false,
                "multi_select" => false,
                "incremental" => false,
                "min_count" => 0,
                "max_count" => 0,
                "min_size" => 0,
                "max_size" => 5000
            ],
            [
                "label" => "Files",
                "key" => "files",
                "folder_id" => null,
                "data_type" => "file",
                "input_type" => "file",
                "default_value" => null,
                "placeholder" => "File here",
                "class" => "file",
                "secure_variable" => false,
                "multi_select" => true,
                "incremental" => false,
                "min_count" => 1,
                "max_count" => 10,
                "min_size" => 0,
                "max_size" => 5000
            ],
            [
                "label" => "Date",
                "key" => "date",
                "folder_id" => null,
                "data_type" => "date",
                "input_type" => "date",
                "default_value" => null,
                "placeholder" => "yyyy-mm-dd",
                "class" => "date-picker",
                "secure_variable" => false,
                "multi_select" => false,
                "incremental" => false,
                "min_count" => 0,
                "max_count" => 0,
                "min_size" => 0,
                "max_size" => 0
            ],
            [
                "label" => "Date Time",
                "key" => "datetime",
                "folder_id" => null,
                "data_type" => "datetime",
                "input_type" => "datetime",
                "default_value" => null,
                "placeholder" => "yyyy-mm-dd",
                "class" => "datetime-picker",
                "secure_variable" => false,
                "multi_select" => false,
                "incremental" => false,
                "min_count" => 0,
                "max_count" => 0,
                "min_size" => 0,
                "max_size" => 0
            ],
            [
                "label" => "Time",
                "key" => "time",
                "folder_id" => null,
                "data_type" => "time",
                "input_type" => "time",
                "default_value" => null,
                "placeholder" => "yyyy-mm-dd",
                "class" => "time-picker",
                "secure_variable" => false,
                "multi_select" => false,
                "incremental" => false,
                "min_count" => 0,
                "max_count" => 0,
                "min_size" => 0,
                "max_size" => 0
            ],
            [
                "label" => "Color",
                "key" => "color",
                "folder_id" => null,
                "data_type" => "color",
                "input_type" => "color",
                "default_value" => null,
                "placeholder" => "#000",
                "class" => "color-picker",
                "secure_variable" => false,
                "multi_select" => false,
                "incremental" => false,
                "min_count" => 0,
                "max_count" => 0,
                "min_size" => 0,
                "max_size" => 0
            ],
            [
                "label" => "Boolean",
                "key" => "bool",
                "folder_id" => null,
                "data_type" => "bool",
                "input_type" => "checkbox",
                "default_value" => null,
                "placeholder" => null,
                "class" => "checkbox",
                "secure_variable" => false,
                "multi_select" => false,
                "incremental" => false,
                "min_count" => 0,
                "max_count" => 0,
                "min_size" => 0,
                "max_size" => 0
            ],
            [
                "label" => "Select",
                "key" => "select",
                "folder_id" => null,
                "data_type" => "array",
                "input_type" => "select",
                "default_value" => null,
                "placeholder" => null,
                "class" => "select",
                "secure_variable" => false,
                "multi_select" => false,
                "incremental" => false,
                "min_count" => 0,
                "max_count" => 0,
                "min_size" => 0,
                "max_size" => 0
            ],
            [
                "label" => "Multi Select",
                "key" => "multi_select",
                "folder_id" => null,
                "data_type" => "array",
                "input_type" => "select",
                "default_value" => null,
                "placeholder" => null,
                "class" => "select",
                "secure_variable" => false,
                "multi_select" => true,
                "incremental" => false,
                "min_count" => 0,
                "max_count" => 0,
                "min_size" => 0,
                "max_size" => 0
            ],
        ];
        collect($templateVariables)->map(fn($templateVariable) => Variable::firstOrCreate(['label' => $templateVariable['label']], $templateVariable));
    }
}
