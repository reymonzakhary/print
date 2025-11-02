<?php

namespace App\Blueprint\Actions;

class ApprovalAction
{
    public function handle($node, $approval, $rejected)
    {
        return $node->config()['Approval'] ? $approval : $rejected;
    }
}
