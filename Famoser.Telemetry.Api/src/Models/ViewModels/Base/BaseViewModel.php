<?php
/**
 * Created by PhpStorm.
 * User: Florian Moser
 * Date: 09.07.2016
 * Time: 14:30
 */

namespace Famoser\MassPass\Models\ViewModels\Base;


use Famoser\MassPass\Helpers\FormatHelper;

class BaseViewModel
{
    protected function formatDateTime($input)
    {
        return FormatHelper::toViewDateTime($input);
    }
}