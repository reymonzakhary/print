<?php

namespace Modules\Cms\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Cms\Entities\ResourceType;

class ResourceTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        // Model::unguard();
        /**
         * @TODO refactor this to use enum instead of the just string
         */
        $resourceTypes = [
            [
                'name' => 'Document',
                'slug' => 'document',
                'mime_type' => 'text/html',
                'file_extensions' => '.html',
                'headers' => null,
                'binary' => false
            ],
            [
                'name' => 'XML',
                'slug' => 'xml',
                'mime_type' => 'text/xml',
                'file_extensions' => '.xml',
                'headers' => null,
                'binary' => false
            ],
            [
                'name' => 'Text',
                'slug' => 'text',
                'mime_type' => 'text/plain',
                'file_extensions' => '.txt',
                'headers' => null,
                'binary' => false
            ],
            [
                'name' => 'CSS',
                'slug' => 'css',
                'mime_type' => 'text/css',
                'file_extensions' => '.css',
                'headers' => null,
                'binary' => false
            ],
            [
                'name' => 'Javascript',
                'slug' => 'javascript',
                'mime_type' => 'text/javascript',
                'file_extensions' => '.js',
                'headers' => null,
                'binary' => false
            ],
            [
                'name' => 'RSS',
                'slug' => 'rss',
                'mime_type' => 'application/rss+xml',
                'file_extensions' => '.rss',
                'headers' => null,
                'binary' => false
            ],
            [
                'name' => 'JSON',
                'slug' => 'json',
                'mime_type' => 'application/json',
                'file_extensions' => '.json',
                'headers' => null,
                'binary' => false
            ],
            [
                'name' => 'PDF',
                'slug' => 'pdf',
                'mime_type' => 'application/pdf',
                'file_extensions' => '.pdf',
                'headers' => null,
                'binary' => true
            ],
            [
                'name' => 'PRODUCT',
                'slug' => 'product',
                'mime_type' => 'application/json',
                'file_extensions' => '.json',
                'headers' => null,
                'binary' => false
            ],
        ];

        ResourceType::where('slug', 'html')->update([
            'name' => 'Document',
            'slug' => 'document',
            'mime_type' => 'text/html',
            'file_extensions' => '.html',
            'headers' => null,
            'binary' => false
        ]);

        collect($resourceTypes)->map(fn($resourceType) => ResourceType::firstOrCreate($resourceType));
    }
}
