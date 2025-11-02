<?php

namespace App\Blueprints\Contracts;

enum BlueprintIncludes: string
{
    case MODEL_PATH = "\\App\\Models\\";
    case TENANT_MODEL_PATH = "\\App\\Models\\Tenants\\";
}
