<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Components\Validator as ValidatorEx;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Str::macro('onlyNumbers', function (string $value) {
            return preg_replace('/[^0-9]/', '', $value);
        });

        Str::macro('matchCode', function (string $value) {
            return Str::remove(['_', '-', ' '], Str::lower(Str::ascii($value)));
        });

        Validator::extend('cpf', function ($attribute, $value, $parameters, $validator) {
            return ValidatorEx::validateCPF($value);
        }, 'The :attribute field are invalid.');

        Validator::extend('cns', function ($attribute, $value, $parameters, $validator) {
            return ValidatorEx::validateCNS($value);
        }, 'The :attribute field are invalid.');
    }
}
