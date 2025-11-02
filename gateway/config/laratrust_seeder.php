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
                'account_orders' => 'l,c,r,u,d,a',
                'contracts' => 'l,c,r,u,d,a',
                'settings' => 'l,r,u,a',
                'plugins' => 'l,c,r,u,d,a',
                'messages' => 'l,c,r,u,d,a',
                'status' => 'l,c,r,u,d,a',
                'lexicons' => 'l,r,u,a',

                'suppliers_settings' => 'l,r,u,a',
                'suppliers' => 'a,l,r',
                'suppliers_categories' => 'a,l,r,c',
                'suppliers_discounts' => 'a,l',
                'warehouses' => 'l,c,r,u,d,a',
                'warehouses_addresses' => 'l,c,r,u,d,a',
                'warehouses_locations' => 'l,c,r,u,d,a',
                'acl' => 'l,c,r,u,d,a',
                'acl_teams_media_sources' => 'l,c,r,u,d,a',
                'roles' => 'l,c,r,u,d,a',
                'acl_roles' => 'l,c,r,u,d,a',
                'acl_permissions' => 'l,c,r,u,d,a',


                'namespaces' => 'l,c,r,u,d,a',
                'members' => 'l,c,r,u,d,a' ,
                'members_addresses' => 'l,c,r,u,d,a',
                'users' => 'l,c,r,u,d,a',
                'users_profiles' => 'l,c,r,u,d,a',
                'users_addresses' => 'l,c,r,u,d,a',
                'users_companies' => 'l,c,r,u,d,a',
                'company_config' => 'l,r,u,a',
                'companies' => 'l,c,r,u,d,a',
                'companies_addresses' => 'l,c,r,u,d,a',
                'companies_teams' => 'l,c,r,u,d,a',
                // Media groups
                'media_sources' => 'l,c,r,u,d,a',
                'tags' => 'l,c,r,u,d,a',
                'media_sources_rules' => 'l,a,c,u,d',


                'quotations' => 'l,c,r,u,d,a',
                'quotations_trashed' => 'a,l,u,r',
                'quotations_media' => 'l,c,d,a',
                'quotations_discount' => 'a,c',
                'quotations_history' => 'r,a,l',
                'quotations_items' => 'a,l,c,u,d',
                'quotations_items_addresses' => 'u',
                'quotations_items-media' => 'l,c,d,u',
                'quotations_items-services' => 'l,c,r,u,d,a',
                'quotations_items-services-media' => 'l,c,d',

                'quotations_services' => 'l,c,r,u,d,a',
                'quotations_services-media' => 'l,c,d',
                'quotations_notifications' => 'a,l,r,c',

                'quotations_type' => 'u',
                'quotations_reference' => 'u',
                'quotations_delivery-multiple' => 'u',
                'quotations_delivery-pickup' => 'u',
                'quotations_note' => 'u',
                'quotations_user' => 'u',
                'quotations_address' => 'u',
                'quotations_decline' => 'u',
                'quotations_invoice-address' => 'u',
                'quotations_items-note' => 'u',
                'quotations_items-reference' => 'u',
                'quotations_items-product' => 'u',
                'quotations_items-product_delivery_days' => 'u',
                'quotations_items-product_prices' => 'u',
                'quotations_items-delivery-separated' => 'u',
                'quotations_items-delivery-pickup' => 'u',
                'quotations_items-producer' => 'u',
                'quotations_items-st' => 'u',
                'quotations_items-st_message' => 'u',

                'design_providers' => 'a,l,r,u',
                'design_providers-templates' => 'l,c,r,u,d,a',
                'provider-templates' => 'l,c,r,u,d,a',

                'invoices' => 'l,c,r,u,d,a',

                'orders' => 'l,c,r,u,d,a',
                'orders_trashed' => 'a,l,u,r',
                'orders_media' => 'l,c,d,a',
                'orders_type' => 'u',
                'orders_reference' => 'u',
                'orders_delivery-multiple' => 'u',
                'orders_delivery-pickup' => 'u',
                'orders_note' => 'u',
                'orders_history' => 'r,a,l',
                'orders_automation' => 'a,l,r,c,u,d',
                'orders_items' => 'a,l,r,c,u,d',
                'orders_items_tags' => 'a,l,r,c,u,d',
                'orders-items-blueprints' => 'a,u',
                'orders_items-produce' => 'a,c,u',
                'orders_items-note' => 'u',
                'orders_items-reference' => 'u',
                'orders_items-product' => 'u',
                'orders_items-product_delivery_days' => 'u',
                'orders_items-product_prices' => 'u',
                'orders_items-delivery-separated' => 'u',
                'orders_items-delivery-pickup' => 'u',
                'orders_items-producer' => 'u',
                'orders_items-st' => 'u',
                'orders_items-st_message' => 'u',
                'orders_items-discount' => 'a,c',
                'orders_items-addresses' => 'u',
                'orders_items-media' => 'c,d,u',
                'orders_items-services' => 'l,c,r,u,d,a',
                'orders_items-services_media' => 'l,c,d',

                'orders_services' => 'l,c,r,u,d,a',
                'orders_discount' => 'a,c,u,l',
                'orders_jobtickets' => 'l,a',
                'orders_services-media' => 'l,c,d',
                'orders_notifications' => 'a,r,c',
                'contexts' => 'a,l,c,r',
                'contexts_addresses' => 'a,l,c,r,u,d',
                'finder' => 'a,l',
                'finder_categories' => 'a,l,c,r,u,d',
                'finder_boxes' => 'a,l,c,r,u',
                'finder_options' => 'a,l,c,r,u',


                'custom-assortments_boxes' => 'a,l,c,r,u,d',
                'custom-assortments_options' => 'a,l,c,r,u,d',
                'custom-assortments_brands' => 'a,l,c,r,u,d',
                'custom-assortments_categories' => 'a,l,c,r,u,d',
                'custom-assortments_products' => 'a,l,c,r,u,d',
                'custom-assortments_products_stocks' => 'a,l,c,r,u,d',
                'custom-assortments_products_variations' => 'a,l,c,r,u,d',
                'custom-assortments_products_variations_stocks' => 'a,l,c,r,u,d',

                'shops' => 'a,l,c,r',
                'shops_categories' => 'a,l,c,r',
                'shops_category_products' => 'a,l,c,r',

                'print-assortments_categories' => 'a,l,c,r,u,d',
                'print-assortments_products' => 'a,l,c,r,u,d',
                'print-assortments_margins' => 'a,l,r,u',
                'print-assortments_boxes' => 'a,l,c,r,u,d',
                'print-assortments_options' => 'a,l,c,r,u,d',
                'print-assortments_boops' => 'a,l,c,r,u,d',
                'print-assortments_combinations' => 'a,l,c,r,u,d',
                'print-assortments_machines' => 'a,l,c,r,u,d',
                'print-assortments_catalogues' => 'a,l,c,r,u,d',
                'print-assortments-system-catalogues' => 'a,l',
                'print-assortments_printing-methods' => 'l,c,r,u,d,a',
                'print-assortments_delivery' => 'a,l,c,r,u,d',


                'margins' => 'a,l,c,u',
                'discounts' => 'a,l,c,r,u,d',
                'services' => 'a,l,c,r,u,d',
                'stocks' => 'a,l,c,r,u,d',
                'teams' => 'a,l,c,r,u,d',
                'teams_users' => 'l,c,r,u,d,a',
                'teams_members' => 'l,c,r,u,d,a',
                'teams_addresses' => 'l,c,r,u,d,a',
                'teams_accessibility' => 'l,c,r,u,d,a',
                'cms' => 'a,l,c,r,u,d',
                'cart' => 'a,l,c,r,u,d',
                'cart-items_media' => 'a,l,c,r,d',
                'campaigns' => 'a,l,c,r,u,d',

                'blueprints_automation' => 'a,l,r,c,u,d',

                'plugins_preflight-conneo' => 'a,l,r,c,u,d',

                'transactions' => 'l,r,a',
                'transactions_logs' => 'l,r,a',
            ],

        ],
        'quotation_supplier' => [
            'permissions' => [
                'auth' => 'a',
                'settings' => 'l,r,u,a',

                'contexts' => 'a,l,r',
                'contexts_addresses' => 'a,l,r,c,u,d',

                'status' => 'l,c,r,u,d,a',

                'quotations' => 'l,r,u,d,a',
                'quotations_media' => 'l,c,d,a',
                'quotations_discount' => 'a,c',
                'quotations_history' => 'r,a,l',
                'quotations_items' => 'a,l,c,u,d',
                'quotations_items_addresses' => 'u',
                'quotations_items-media' => 'l,c,d',
                'quotations_items-services' => 'l,c,r,u,d,a',
                'quotations_items-services-media' => 'l,c,d',

                'quotations_services' => 'l,c,r,u,d,a',
                'quotations_services-media' => 'l,c,d',
                'quotations_notifications' => 'a,l,r,c',

                'quotations_type' => 'u',
                'quotations_reference' => 'u',
                'quotations_delivery-multiple' => 'u',
                'quotations_delivery-pickup' => 'u',
                'quotations_note' => 'u',
                'quotations_user' => 'u',
                'quotations_address' => 'u',
                'quotations_decline' => 'u',
                'quotations_invoice-address' => 'u',
                'quotations_items-note' => 'u',
                'quotations_items-reference' => 'u',
                'quotations_items-product' => 'u',
                'quotations_items-product_delivery_days' => 'u',
                'quotations_items-product_prices' => 'u',
                'quotations_items-delivery-separated' => 'u',
                'quotations_items-delivery-pickup' => 'u',
                'quotations_items-producer' => 'u',
                'quotations_items-st' => 'u',
                'quotations_items-st_message' => 'u',
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
