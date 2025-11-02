<?php

namespace App\Blueprint\Actions\Traits;

use App\Events\Tenant\Report\CreateReportEvent;

trait HasReportingTraits
{
    public function createReport(string $title, array $activity, $request)
    {
        event(
            new CreateReportEvent(
                'product id: ' . $request->product->id . ' - Action: ' . $title,
                $activity,
                $request->resolution,
                $request->product->name,
                $request->tenant->user
            )
        );
    }
}
