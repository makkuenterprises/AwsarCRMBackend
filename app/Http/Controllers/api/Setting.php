<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Details;
use Illuminate\Support\Facades\Auth;


class Setting extends Controller
{
    //
    public function setKeyValue()
{
    // Retrieve the value from the Setting model

//     MAIL_MAILER=smtp
// MAIL_HOST=mailpit
// MAIL_PORT=1025
// MAIL_USERNAME=null
// MAIL_PASSWORD=null
// MAIL_ENCRYPTION=null
// MAIL_FROM_ADDRESS="hello@example.com"
// MAIL_FROM_NAME="${APP_NAME}"

    $setting = Details::first();

    $MAIL_USERNAME = Details::where('key', 'YOUR_KEY')->first();
    $MAIL_PASSWORD = Details::where('key', 'YOUR_KEY')->first();
    $MAIL_PORT = Details::where('key', 'YOUR_KEY')->first();
    $MAIL_HOST = Details::where('key', 'YOUR_KEY')->first();
    $MAIL_FROM_ADDRESS = Details::where('key', 'YOUR_KEY')->first();
    $MAIL_FROM_NAME = Details::where('key', 'YOUR_KEY')->first();

    if ($setting) {
        // Set the environment variable dynamically for the current request
          $MAIL_USERNAME =  $setting->smtp_username;
          $MAIL_PASSWORD =  $setting->smtp_password;
          $MAIL_PORT =  $setting->smtp_ports;
          $MAIL_HOST =  $setting->smtp_host;
          $MAIL_FROM_ADDRESS =  $setting->email;
          $MAIL_FROM_NAME =  $setting->	business_name;
          $METHOD =  $setting->	method;
          $BASE_URL =  $setting-> base_url;

     
        putenv("MAIL_USERNAME=$MAIL_USERNAME");
        putenv("MAIL_PASSWORD=$MAIL_PASSWORD");
        putenv("MAIL_PORT=$MAIL_PORT");
        putenv("MAIL_HOST=$MAIL_HOST");
        putenv("MAIL_FROM_ADDRESS=$email");
        putenv("MAIL_FROM_NAME=$MAIL_FROM_NAME");
        putenv("METHOD=$METHOD");
        putenv("BASE_URL=$BASE_URL");

        // Alternatively, you can use env() helper, but it will not persist the change
        // beyond the current request
        env('MAIL_USERNAME', $MAIL_USERNAME);
        env('MAIL_PASSWORD', $MAIL_PASSWORD);
        env('MAIL_PORT', $MAIL_PORT);
        env('MAIL_HOST', $MAIL_HOST);
        env('MAIL_FROM_ADDRESS', $MAIL_FROM_ADDRESS);
        env('MAIL_FROM_NAME', $MAIL_FROM_NAME);
        env('METHOD', $METHOD);
        env('BASE_URL', $BASE_URL);
    }

    // You can now use the environment variable
    // $envValue = env('YOUR_ENV_VARIABLE');
    // Do something with $envValue
}
}
