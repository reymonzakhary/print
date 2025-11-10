<?php

namespace Database\Seeders\Tenants;

use App\Models\Tenant\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $statues = [
            [
                'code' => '300',
                'name' => 'draft',
                'description' => 'This item is being created.'
            ],
            [
                'code' => '301',
                'name' => 'pending',
                'description' => 'Awaiting the result of the action.'
            ],
            [
                'code' => '302',
                'name' => 'new',
                'description' => 'This is a new item.'
            ],
            [
                'code' => '303',
                'name' => 'in_progress',
                'description' => 'Item is in progress.'
            ],
            [
                'code' => '304',
                'name' => 'being_shipped',
                'description' => 'Item is being shipped to customer.'
            ],
            [
                'code' => '305',
                'name' => 'canceled',
                'description' => 'Item is canceled. This can have multiple causes.'
            ],
            [
                'code' => '306',
                'name' => 'ready',
                'description' => 'Item is ready to be picked-up.'
            ],
            [
                'code' => '307',
                'name' => 'delivered',
                'description' => 'Item is delivered to customer.'
            ],
            [
                'code' => '308',
                'name' => 'done',
                'description' => 'This process is finished.'
            ],
            [
                'code' => '309',
                'name' => 'locked',
                'description' => 'Item has been locked temporarily.'
            ],
            [
                'code' => '310',
                'name' => 'archived',
                'description' => 'Process is finished and item is archived.'
            ],
            [
                'code' => '311',
                'name' => 'blocked',
                'description' => 'Item is blocked because something happened.'
            ],
            [
                'code' => '312',
                'name' => 'mailing',
                'description' => 'Waiting until the customer responds to the e-mail.'
            ],
            [
                'code' => '313',
                'name' => 'mailed',
                'description' => 'E-mail has been sent to customer.'
            ],
            [
                'code' => '314',
                'name' => 'processing',
                'description' => 'Item is being processed.'
            ],
            [
                'code' => '315',
                'name' => 'expiring',
                'description' => 'Item is awaiting respond from customer and the expiry date is almost exceeded.'
            ],
            [
                'code' => '316',
                'name' => 'expired',
                'description' => 'The expiry date is exceeded.'
            ],
            [
                'code' => '317',
                'name' => 'editable',
                'description' => 'Item can be edited.'
            ],
            [
                'code' => '318',
                'name' => 'in_production',
                'description' => 'Item is in production.'
            ],
            [
                'code' => '319',
                'name' => 'rejected',
                'description' => 'This order/item has been rejected.'
            ],
            [
                'code' => '320',
                'name' => 'accepted',
                'description' => 'invitation has ben accepted.'
            ],
            [
                'code' => '321',
                'name' => 'failed',
                'description' => 'the processes has failed.'
            ],
            [
                'code' => '322',
                'name' => 'waiting_for_response',
                'description' => 'waiting for supplier\'s response.'
            ],
            [
                'code' => '323',
                'name' => 'declined',
                'description' => 'This order/item has been declined.'
            ],
            [
                'code' => '324',
                'name' => 'editing',
                'description' => 'This order/item is being edited.'
            ],
        ];

        foreach ($statues as $status) {
            Status::firstOrCreate(['code' => $status['code']], $status);
        }
    }
}


