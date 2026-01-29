<?php

namespace Masri\Deployer;

use Illuminate\Support\ServiceProvider;
use Masri\Deployer\Commands\InitDeployment;

class DeployerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InitDeployment::class,
            ]);
        }
    }

    public function register()
    {
        //
    }
}