<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     *
     * Selenium runs in a sibling container (see docker-compose.dusk.yml),
     * so there is no local chromedriver to boot.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        // No-op: the browser lives in the `selenium` service.
    }

    /**
     * Create the RemoteWebDriver instance pointed at the Selenium container.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://selenium:4444',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Determine whether the Dusk command has disabled headless mode.
     */
    protected function hasHeadlessDisabled(): bool
    {
        return isset($_SERVER['DUSK_HEADLESS_DISABLED']) ||
               isset($_ENV['DUSK_HEADLESS_DISABLED']);
    }
}
