<?php

namespace App\Services\Settings;

use App\Settings\GeneralSettings;
use Exception;
use Illuminate\Support\Facades\DB;

class Config
{
    public static function get(string $config): mixed
    {
        try {
            DB::connection()->getPdo();

            return app(GeneralSettings::class)->{$config};
        } catch (Exception) {}

        return null;
    }
}