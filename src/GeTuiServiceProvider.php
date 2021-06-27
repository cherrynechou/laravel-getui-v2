<?php
/**
 * Created by : PhpStorm
 * User: cherrynechou
 * Date: 2021/6/27 0027
 * Time: 19:35
 */
namespace CherryneChou\GeTui;

use Illuminate\Support\ServiceProvider;

/**
 * Class GeTui
 * @package CherryneChou\GeTui
 */
class GeTuiServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
      if ($this->app->runningInConsole()) {
          $this->publishes([ __DIR__.'/../config' => config_path() ], 'getui.php' );
      }
    }
}
