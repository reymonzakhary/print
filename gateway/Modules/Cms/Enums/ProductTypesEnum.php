<?php
namespace Modules\Cms\Enums;

enum ProductTypesEnum : string
{
    case HTML = 'HTML';
    case XML = 'XML';
    case Text = 'Text';
    case CSS = 'CSS';
    case Javascript = 'Javascript';
    case RSS = 'RSS';
    case JSON = 'JSON';
    case PDF = 'PDF';
    case PRODUCT = 'PRODUCT';
}