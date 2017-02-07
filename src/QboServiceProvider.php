<?php

namespace sdavis1902\QboLaravel;

use Illuminate\Support\ServiceProvider;

class QboServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        \App::bind('qbo', function(){
            return new \sdavis1902\QboLaravel\Qbo;
        });
    }
}
