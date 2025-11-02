<?php

return [
    /**
     * Control if the seeder should create a user per role while seeding the data.
     */
    'create_users' => false,

    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'roles_structure' => [
        'superadministrator' => [
            'permissions' => [
                'auth' => 'a',
                'account' => 'l,r,u,a',
                'tenants' => 'l,c,r,u,d,a',
                'users' => 'l,c,r,u,d,a',
                'clients' => 'l,c,r,u,d,a',
                'clients_media' => 'l,r,c,a',
                'company' => 'l,r,u,a',
                'apps' => 'l,r,a',
                'countries' => 'l,r,a',
                'companies' => 'l,c,r,u,d,a',
                'companies_contracts' => 'l,c,r,u,d,a',
                'companies_contracts_quotations' => 'l,c,r,u,a',
                'categories' => 'l,c,r,u,a,d',
                'categories_manifest' => 'l,c,r,u,a,d',
                'boxes' => 'l,c,r,u,a,d',
                'options' => 'l,c,r,u,a,d',
                'messages' => 'l,c,r,u,a,d',
                'currencies' => 'l,c,r,u,a,d',
            ],

        ],
        'external' => [
            'permissions' => [
                'auth' => 'a',
                'account' => 'l,r,u,a',
                'company' => 'l,r,u,a',
                'companies_contracts' => 'l,c,r,u,d,a',
                'companies_contracts_quotations' => 'l,c,r,u,a',
                'currencies' => 'l,r,a',

            ],

        ]
    ],

    'permissions_map' => [
        'l' => 'list',
        'a' => 'access',
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete'
    ],
];
