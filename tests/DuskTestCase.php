<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use Symfony\Component\Process\Process;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Background web server process started automatically when port 8000 is not in use.
     */
    protected static ?Process $webServerProcess = null;

    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
            static::ensureWebServerRunning();
        }
    }

    /**
     * Ensure the application's database is seeded before each browser test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (! User::where('email', 'admin@gmail.com')->exists()) {
            $this->seed(DatabaseSeeder::class);
        }
    }

    /**
     * Determine the application's base URL for Dusk tests.
     */
    protected function baseUrl(): string
    {
        return 'http://127.0.0.1:8000';
    }

    /**
     * Automatically start the local Laravel server on 127.0.0.1:8000 if not already running.
     */
    protected static function ensureWebServerRunning(): void
    {
        $socket = @fsockopen('127.0.0.1', 8000, $errno, $errstr, 0.5);
        if (is_resource($socket)) {
            fclose($socket);
            return;
        }

        $basePath = dirname(__DIR__);
        $serverScript = $basePath . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'laravel' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Illuminate' . DIRECTORY_SEPARATOR . 'Foundation' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'server.php';

        static::$webServerProcess = new Process(
            [PHP_BINARY, '-S', '127.0.0.1:8000', $serverScript],
            $basePath . DIRECTORY_SEPARATOR . 'public',
            ['APP_URL' => 'http://127.0.0.1:8000']
        );
        static::$webServerProcess->disableOutput();
        static::$webServerProcess->start();

        register_shutdown_function(function () {
            if (static::$webServerProcess && static::$webServerProcess->isRunning()) {
                static::$webServerProcess->stop(1);
            }
        });

        // Wait up to 5 seconds for the built-in server to accept connections
        for ($i = 0; $i < 50; $i++) {
            $check = @fsockopen('127.0.0.1', 8000, $errno, $errstr, 0.2);
            if (is_resource($check)) {
                fclose($check);
                break;
            }
            usleep(100000);
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
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
                '--no-sandbox',
                '--disable-dev-shm-usage',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
