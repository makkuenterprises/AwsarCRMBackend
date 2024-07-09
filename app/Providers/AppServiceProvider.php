<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Details;
use Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        //   Passport::ignoreRoutes();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void 
    {                                                                                                                                                                    
    //     $setting = Details::first();
    //     if($setting){
    //     {
    //         $data =  [
    //         // 'url' => $setting-> base_url,
    //         'host' =>  $setting->smtp_host,
    //         'port' =>  $setting->smtp_ports,
    //         'encryption' => $setting->method,
    //         'username' =>  $setting->smtp_username,
    //         'password' => $setting->smtp_password,
    //         'from'    =>[ 
    //             'address' => $setting->email,
    //             'name'  => $setting->business_name,
    //         ]
    //     ];
    //     Config::set('mail', $data);
    // }
    // }
    }
}
