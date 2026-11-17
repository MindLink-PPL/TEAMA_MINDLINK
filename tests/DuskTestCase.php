<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            '--no-sandbox',
            '--disable-gpu',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--headless=new',
            ]);
        })->all());

        $driverUrl = $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://127.0.0.1:9515';

        // Allow teammates using ChromeDriver to set DUSK_BROWSER=chrome in their .env
        if (env('DUSK_BROWSER', 'edge') === 'chrome') {
            return RemoteWebDriver::create(
                $driverUrl,
                DesiredCapabilities::chrome()->setCapability(
                    ChromeOptions::CAPABILITY,
                    $options
                )
            );
        }

        // Default to Microsoft Edge on Windows
        $edgeBinary = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
        if (file_exists($edgeBinary)) {
            $options->setBinary($edgeBinary);
        } else {
            $edgeBinary64 = 'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe';
            if (file_exists($edgeBinary64)) {
                $options->setBinary($edgeBinary64);
            }
        }

        $capabilities = new DesiredCapabilities([
            WebDriverCapabilityType::BROWSER_NAME => 'MicrosoftEdge',
            'ms:edgeOptions' => $options->toArray(),
        ]);

        return RemoteWebDriver::create($driverUrl, $capabilities);
    }

    /**
     * Determine the default base URL for Dusk tests.
     */
    protected function baseUrl(): string
    {
        if (! empty($_ENV['DUSK_START_URL'])) {
            return rtrim($_ENV['DUSK_START_URL'], '/');
        }

        $appUrl = rtrim(config('app.url', 'http://127.0.0.1:8000'), '/');
        $parsed = parse_url($appUrl);
        $host = $parsed['host'] ?? '127.0.0.1';
        $port = $parsed['port'] ?? (($parsed['scheme'] ?? 'http') === 'https' ? 443 : 80);

        $fp = @fsockopen($host, $port, $errno, $errstr, 0.5);
        if ($fp) {
            fclose($fp);
            return $appUrl;
        }

        // Fallback to Laragon Apache port 80 if artisan serve is not running
        $fpLaragon = @fsockopen('127.0.0.1', 80, $errno, $errstr, 0.5);
        if ($fpLaragon) {
            fclose($fpLaragon);
            return 'http://localhost/TEAMA_MINDLINK-ALFA_SPRINT1-GIT/public';
        }

        return $appUrl;
    }
}