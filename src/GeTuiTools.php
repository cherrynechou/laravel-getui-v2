<?php
/**
 * Created by : PhpStorm
 * User: cherrynechou
 * Date: 2021/6/27 0027
 * Time: 19:35
 */
namespace CherryneChou\GeTui;
use Illuminate\Support\Facades\Cache;

/**
 * Class GeTuiTools
 * @package CherryneChou\GeTui
 */
class GeTuiTools
{
    public function static clearGetuiObject()
    {
        Cache::forget(config('getui.object_class'));
    }
}
