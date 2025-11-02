<?php

namespace App\Http\Controllers\Tenant\Mgr\Orders\JobTickets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Items\JobTicketStoreRequest;
use App\Models\Tenants\Order;
use App\Processors\JobTicketType\XmlJobTicketProcessor;

class JobTicketController extends Controller
{
    public function __invoke(
        JobTicketStoreRequest $request,
        Order $order
    )
    {
        return XmlJobTicketProcessor::formatOrder($order,
            $request->get('iso'),
            $request->get('format')
        );
    }
}
