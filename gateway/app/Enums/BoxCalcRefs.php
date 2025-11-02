<?php

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum BoxCalcRefs: string
{
    use EnumConcern;

    case OTHER = 'other';
    case FORMAT = 'format';
    case WEIGHT = 'weight';
    case MATERIAL = 'material';
    case PRINTING_COLORS = 'printing_colors';
    case LAMINATION = 'lamination';
    case PAGES = 'pages';
    case SIDES = 'sides';
    case COVER = 'cover';
    case FOLDING = 'folding';
    case BINDING_DIRECTION = 'binding_direction';
    case BINDING_Method = 'binding_method';
    case BINDING_COLOR = 'binding_color';
    case BINDING_MATERIAL = 'binding_material';
    case ENDPAPERS = 'endpapers';
}
