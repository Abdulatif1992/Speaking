<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\JapaneseReading\JapaneseReadingInterface;
use App\Services\JapaneseReading\MecabCliReading;
use App\Services\JapaneseReading\KanaOnlyReading;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $useMecab = env('JP_READING_ENGINE', 'kana') === 'mecab';

        if ($useMecab) {
            $this->app->bind(JapaneseReadingInterface::class, function () {
                return new MecabCliReading(
                    mecabExePath: env('MECAB_EXE_PATH'),
                    dicPath: env('MECAB_DIC_PATH') ?: null
                );
            });
        } else {
            $this->app->bind(JapaneseReadingInterface::class, KanaOnlyReading::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
