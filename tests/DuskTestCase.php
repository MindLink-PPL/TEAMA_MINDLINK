<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\ElementClickInterceptedException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Laravel\Dusk\ElementResolver;
use Laravel\Dusk\OperatingSystem;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Constraint\RegularExpression;
use RuntimeException;
use Throwable;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Background ChromeDriver process handle started via proc_open.
     *
     * @var resource|null
     */
    protected static $chromeDriverHandle = null;

    /**
     * Background web server process handle started via proc_open.
     *
     * @var resource|null
     */
    protected static $webServerHandle = null;

    /**
     * Port bound by the active ChromeDriver instance.
     */
    protected static int $chromeDriverPort = 9515;

    /**
     * Whether the process shutdown cleanup handler has been registered.
     */
    protected static bool $shutdownRegistered = false;

    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        static::configureProxyEnvironment();
        static::ensureWritableTempDirectory();

        if (! static::runningInSail()) {
            static::ensureChromeDriverRunning();
            static::ensureWebServerRunning();
            static::registerProcessShutdown();
        }
    }

    /**
     * Ensure the application's database is seeded and configured before each browser test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') === 'sqlite' && config('database.connections.sqlite.database') === ':memory:') {
            config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
            DB::purge('sqlite');
        }

        if (! User::where('email', 'admin@gmail.com')->exists()) {
            $this->seed(DatabaseSeeder::class);
        }

        if (! $this->shouldStartMaximized() && ! empty(static::$browsers)) {
            $primary = collect(static::$browsers)->first();
            if ($primary instanceof Browser) {
                $primary->resize(1920, 1080);
            }
        }
    }

    /**
     * Determine the application's base URL for Dusk tests.
     */
    protected function baseUrl(): string
    {
        if (! empty($_ENV['DUSK_START_URL'])) {
            return rtrim($_ENV['DUSK_START_URL'], '/');
        }

        return 'http://127.0.0.1:8000';
    }

    /**
     * Ensure local WebDriver and HTTP requests bypass any proxy settings.
     */
    protected static function configureProxyEnvironment(): void
    {
        putenv('NO_PROXY=localhost,127.0.0.1');
        putenv('HTTP_PROXY=');
        putenv('HTTPS_PROXY=');

        $_ENV['NO_PROXY'] = 'localhost,127.0.0.1';
        $_SERVER['NO_PROXY'] = 'localhost,127.0.0.1';
        $_ENV['HTTP_PROXY'] = '';
        $_SERVER['HTTP_PROXY'] = '';
        $_ENV['HTTPS_PROXY'] = '';
        $_SERVER['HTTPS_PROXY'] = '';
    }

    /**
     * Ensure a writable workspace temp directory exists and point TEMP, TMP, and TMPDIR to it.
     */
    protected static function ensureWritableTempDirectory(): string
    {
        $tempDir = dirname(__DIR__)
            . DIRECTORY_SEPARATOR . 'storage'
            . DIRECTORY_SEPARATOR . 'framework'
            . DIRECTORY_SEPARATOR . 'testing'
            . DIRECTORY_SEPARATOR . 'tmp';

        if (! is_dir($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        putenv("TEMP={$tempDir}");
        putenv("TMP={$tempDir}");
        putenv("TMPDIR={$tempDir}");

        $_ENV['TEMP'] = $tempDir;
        $_SERVER['TEMP'] = $tempDir;
        $_ENV['TMP'] = $tempDir;
        $_SERVER['TMP'] = $tempDir;
        $_ENV['TMPDIR'] = $tempDir;
        $_SERVER['TMPDIR'] = $tempDir;

        return $tempDir;
    }

    /**
     * Build a clean subprocess environment with a guaranteed writable temp directory and no local proxy interference.
     *
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    protected static function buildCleanEnvironment(array $overrides = []): array
    {
        $tempDir = static::ensureWritableTempDirectory();
        $cleanEnv = [];

        foreach (getenv() as $key => $value) {
            if (! in_array(strtolower((string) $key), ['temp', 'tmp', 'tmpdir', 'http_proxy', 'https_proxy', 'no_proxy'], true)) {
                $cleanEnv[$key] = $value;
            }
        }

        $cleanEnv['TEMP'] = $tempDir;
        $cleanEnv['TMP'] = $tempDir;
        $cleanEnv['TMPDIR'] = $tempDir;
        $cleanEnv['NO_PROXY'] = 'localhost,127.0.0.1';

        if (OperatingSystem::onWindows()) {
            $cleanEnv['SystemRoot'] = getenv('SystemRoot') ?: 'C:\\WINDOWS';
        }

        return array_merge($cleanEnv, $overrides);
    }

    /**
     * Check whether a local TCP port is already accepting connections.
     */
    protected static function isPortOpen(string $host, int $port): bool
    {
        $socket = @fsockopen($host, $port, $errno, $errstr, 0.2);
        if (is_resource($socket)) {
            fclose($socket);

            return true;
        }

        return false;
    }

    /**
     * Wait up to the given timeout for a local TCP port to accept connections.
     */
    protected static function waitForPort(string $host, int $port, int $timeoutSeconds = 5): void
    {
        $iterations = $timeoutSeconds * 10;

        for ($i = 0; $i < $iterations; $i++) {
            if (static::isPortOpen($host, $port)) {
                return;
            }
            usleep(100000);
        }
    }

    /**
     * Return null stdio descriptors so background child processes never block pipe reads or exits on Windows.
     *
     * @return array<int, array<int, string>>
     */
    protected static function nullDescriptors(): array
    {
        $nullDevice = DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';

        return [
            0 => ['file', $nullDevice, 'r'],
            1 => ['file', $nullDevice, 'w'],
            2 => ['file', $nullDevice, 'w'],
        ];
    }

    /**
     * Automatically start a clean WebDriver instance once per suite with a writable temp directory.
     */
    protected static function ensureChromeDriverRunning(): void
    {
        if (is_resource(static::$chromeDriverHandle) && static::isPortOpen('127.0.0.1', static::$chromeDriverPort)) {
            return;
        }

        if (static::$chromeDriverHandle === null && static::isPortOpen('127.0.0.1', 9515)) {
            if (OperatingSystem::onWindows()) {
                @exec('taskkill /F /IM chromedriver-win.exe 2>NUL');
                @exec('taskkill /F /IM msedgedriver.exe 2>NUL');
                usleep(100000);
            }
        }

        static::$chromeDriverPort = 9515;
        foreach (range(9515, 9525) as $candidatePort) {
            if (! static::isPortOpen('127.0.0.1', $candidatePort)) {
                static::$chromeDriverPort = $candidatePort;
                break;
            }
        }

        $basePath = dirname(__DIR__);
        $driverPath = static::$chromeDriver;

        if (! $driverPath) {
            $filenames = [
                'linux' => 'chromedriver-linux',
                'linux-arm' => 'chromedriver-linux-arm',
                'mac' => 'chromedriver-mac',
                'mac-intel' => 'chromedriver-mac-intel',
                'mac-arm' => 'chromedriver-mac-arm',
                'win' => 'chromedriver-win.exe',
            ];

            $driverPath = $basePath . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'laravel' . DIRECTORY_SEPARATOR . 'dusk' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . $filenames[OperatingSystem::id()];
        }

        $resolved = realpath($driverPath);
        if ($resolved === false) {
            throw new RuntimeException("Invalid path to WebDriver [{$driverPath}].");
        }

        $env = static::buildCleanEnvironment(OperatingSystem::onWindows() || OperatingSystem::onMac() ? [] : [
            'DISPLAY' => $_ENV['DISPLAY'] ?? ':0',
        ]);

        $handle = proc_open(
            [$resolved, '--port=' . static::$chromeDriverPort],
            static::nullDescriptors(),
            $pipes,
            $basePath,
            $env
        );

        if (! is_resource($handle)) {
            throw new RuntimeException('Unable to start WebDriver process.');
        }

        static::$chromeDriverHandle = $handle;
        static::waitForPort('127.0.0.1', static::$chromeDriverPort, 5);
    }

    /**
     * Automatically start the local Laravel server on 127.0.0.1:8000 once per suite if not already running.
     */
    protected static function ensureWebServerRunning(): void
    {
        if (static::isPortOpen('127.0.0.1', 8000)) {
            return;
        }

        $basePath = dirname(__DIR__);
        $serverScript = $basePath . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'laravel' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Illuminate' . DIRECTORY_SEPARATOR . 'Foundation' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'server.php';

        $env = static::buildCleanEnvironment([
            'APP_URL' => 'http://127.0.0.1:8000',
            'DB_CONNECTION' => getenv('DB_CONNECTION') ?: 'mysql',
            'DB_HOST' => getenv('DB_HOST') ?: '127.0.0.1',
            'DB_PORT' => getenv('DB_PORT') ?: '3308',
            'DB_DATABASE' => getenv('DB_DATABASE') ?: 'ppl_mindlink',
            'DB_USERNAME' => getenv('DB_USERNAME') ?: 'root',
            'DB_PASSWORD' => getenv('DB_PASSWORD') ?: '',
            'CACHE_STORE' => 'file',
            'SESSION_DRIVER' => 'file',
        ]);

        $handle = proc_open(
            [PHP_BINARY, '-S', '127.0.0.1:8000', $serverScript],
            static::nullDescriptors(),
            $pipes,
            $basePath . DIRECTORY_SEPARATOR . 'public',
            $env
        );

        if (! is_resource($handle)) {
            throw new RuntimeException('Unable to start local PHP web server process.');
        }

        static::$webServerHandle = $handle;
        static::waitForPort('127.0.0.1', 8000, 5);
    }

    /**
     * Register a single shutdown hook to terminate the background server and WebDriver processes cleanly.
     */
    protected static function registerProcessShutdown(): void
    {
        if (static::$shutdownRegistered) {
            return;
        }

        static::$shutdownRegistered = true;

        register_shutdown_function(function () {
            try {
                static::closeAll();
            } catch (Throwable) {
                // Ignore browser cleanup failures during process shutdown.
            }

            foreach ([static::$webServerHandle, static::$chromeDriverHandle] as $handle) {
                if (is_resource($handle)) {
                    @proc_terminate($handle);
                    @proc_close($handle);
                }
            }

            static::$webServerHandle = null;
            static::$chromeDriverHandle = null;
        });
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

        $driverUrl = $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? ('http://127.0.0.1:' . static::$chromeDriverPort);

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
     * Create a new Browser instance tailored for MindLink layouts and browser navigation timing.
     */
    protected function newBrowser($driver): Browser
    {
        $browser = new class($driver) extends Browser
        {
            public function __construct($driver, $resolver = null)
            {
                $prefix = $resolver instanceof ElementResolver ? $resolver->prefix : 'body';

                parent::__construct($driver, $this->makeElementResolver($driver, $prefix));

                $this->disableFitOnFailure();
            }

            /**
             * Override jQuery injection so Dusk never fails if vendor/laravel/dusk/bin/jquery.js is absent.
             */
            public function ensurejQueryIsAvailable()
            {
                // Native DOM queries are used instead of jQuery.
            }

            /**
             * Build an ElementResolver that matches uppercase CSS-transformed button labels.
             */
            protected function makeElementResolver($driver, string $prefix = 'body'): ElementResolver
            {
                return new class($driver, $prefix) extends ElementResolver
                {
                    protected function findButtonByText($button)
                    {
                        if (! is_null($element = parent::findButtonByText($button))) {
                            return $element;
                        }

                        $buttons = $this->all('button');

                        foreach ($buttons as $element) {
                            if (strcasecmp(trim($element->getText()), $button) === 0) {
                                return $element;
                            }
                        }

                        foreach ($buttons as $element) {
                            if (Str::contains($element->getText(), $button, true)) {
                                return $element;
                            }
                        }

                        foreach ($buttons as $element) {
                            $textContent = trim(preg_replace('/\s+/', ' ', (string) $element->getAttribute('textContent')));

                            if (strcasecmp($textContent, $button) === 0 || Str::contains($textContent, $button, true)) {
                                return $element;
                            }
                        }

                        return null;
                    }
                };
            }

            /**
             * Center-scroll before pressing a button and wait for full-page form submission redirects if triggered.
             */
            public function press($button)
            {
                $element = $this->resolver->resolveForButtonPress($button);

                return $this->clickElementSafely($element);
            }

            /**
             * Center-scroll before clicking a selector and wait for full-page form submission redirects if triggered.
             */
            public function click($selector = null)
            {
                if (is_null($selector)) {
                    return parent::click($selector);
                }

                foreach ($this->resolver->all($selector) as $element) {
                    try {
                        return $this->clickElementSafely($element);
                    } catch (ElementClickInterceptedException $e) {
                        // Try the next matching element if the first one is intercepted.
                    }
                }

                throw $e ?? new NoSuchElementException("Unable to locate element with selector [{$selector}].");
            }

            /**
             * Click a link by visible text using native DOM queries (without requiring jquery.js).
             */
            public function clickLink($link, $element = 'a')
            {
                $selector = $this->resolver->format($element);

                $clicked = $this->driver->executeScript("
                    const selector = arguments[0];
                    const targetText = arguments[1].trim();
                    const nodes = Array.from(document.querySelectorAll(selector));
                    const match = nodes.find(el => {
                        const txt = (el.innerText || el.textContent || '').replace(/\\s+/g, ' ').trim();
                        return txt === targetText || txt.toLowerCase() === targetText.toLowerCase() || txt.includes(targetText);
                    });

                    if (!match) {
                        return false;
                    }

                    window.__duskPreClick = true;
                    const href = match.getAttribute('href') || '';
                    const willNavigate = href !== '' && !href.startsWith('#') && !href.startsWith('javascript:');
                    match.scrollIntoView({ block: 'center', inline: 'nearest' });
                    match.click();
                    return willNavigate;
                ", [$selector, $link]);

                if ($clicked === false) {
                    throw new NoSuchElementException("Unable to locate link [{$link}] with selector [{$selector}].");
                }

                if ($clicked === true) {
                    $this->waitForPendingNavigation();
                }

                return $this;
            }

            /**
             * Wait up to 5 seconds for the expected text, retrying across transient DOM swaps and uppercase styling.
             */
            public function assertSeeIn($selector, $text, $ignoreCase = false)
            {
                if ($text === '') {
                    return $this->assertSeeNothingIn($selector);
                }

                $fullSelector = $this->resolver->format($selector);
                $deadline = microtime(true) + 5.0;
                $lastText = '';

                while (true) {
                    try {
                        $element = $this->resolver->findOrFail($selector);
                        $renderedText = $element->getText();
                        $rawText = (string) $element->getAttribute('textContent');

                        $matched = Str::contains($renderedText, $text, $ignoreCase)
                            || Str::contains($renderedText, $text, true)
                            || ($renderedText !== '' && Str::contains($rawText, $text, $ignoreCase));

                        if ($matched) {
                            Assert::assertTrue(true);

                            return $this;
                        }

                        $lastText = $renderedText;
                    } catch (StaleElementReferenceException|NoSuchElementException) {
                        // Retry while navigation or reactive DOM update settles.
                    }

                    if (microtime(true) >= $deadline) {
                        break;
                    }

                    usleep(100000);
                }

                Assert::assertTrue(
                    false,
                    "Did not see expected text [{$text}] within element [{$fullSelector}]. Actual text: [" . Str::limit(trim($lastText), 200) . ']'
                );

                return $this;
            }

            /**
             * Wait up to 5 seconds for the URL path to match after redirects.
             */
            public function assertPathIs($path)
            {
                $pattern = str_replace('\*', '.*', preg_quote($path, '/'));
                $deadline = microtime(true) + 5.0;
                $actualPath = '';

                while (true) {
                    try {
                        $actualPath = parse_url($this->driver->getCurrentURL(), PHP_URL_PATH) ?? '';
                        if (preg_match('/^' . $pattern . '$/u', $actualPath) === 1) {
                            break;
                        }
                    } catch (Throwable) {
                        // Retry while navigation settles.
                    }

                    if (microtime(true) >= $deadline) {
                        break;
                    }

                    usleep(100000);
                }

                Assert::assertThat(
                    $actualPath,
                    new RegularExpression('/^' . $pattern . '$/u'),
                    "Actual path [{$actualPath}] does not equal expected path [{$path}]."
                );

                return $this;
            }

            /**
             * Scroll an element into the center of the viewport before clicking.
             * Only poll for form-submit navigation if the clicked element can actually submit a form
             * (prevents executeScript from auto-dismissing window.prompt / window.confirm dialogs).
             */
            protected function clickElementSafely($element): static
            {
                $canSubmitForm = (bool) $this->driver->executeScript("
                    const el = arguments[0];
                    el.scrollIntoView({ block: 'center', inline: 'nearest' });

                    const inForm = el.closest('form') !== null;
                    const tag = (el.tagName || '').toLowerCase();
                    const type = (el.getAttribute('type') || '').toLowerCase();
                    const hasOnClickDialog = /prompt\\(|confirm\\(|alert\\(|rescheduleAppointment|cancelAppointment/.test(el.getAttribute('onclick') || '');

                    if (hasOnClickDialog) {
                        return false;
                    }

                    if (inForm && (type === 'submit' || (tag === 'button' && type !== 'button'))) {
                        window.__duskPreClick = true;
                        window.__duskFormSubmitted = false;
                        if (window.__duskSubmitHandler) {
                            document.removeEventListener('submit', window.__duskSubmitHandler);
                        }
                        window.__duskSubmitHandler = function (e) {
                            if (!e.defaultPrevented) {
                                window.__duskFormSubmitted = true;
                            }
                        };
                        document.addEventListener('submit', window.__duskSubmitHandler, { once: true });
                        return true;
                    }

                    return false;
                ", [$element]);

                $element->click();

                if ($canSubmitForm) {
                    try {
                        $submitted = $this->driver->executeScript('return window.__duskFormSubmitted === true;');
                    } catch (Throwable) {
                        $submitted = false;
                    }

                    if ($submitted) {
                        $this->waitForPendingNavigation();
                    }
                }

                return $this;
            }

            /**
             * Wait until a triggered navigation replaces the pre-click window and reaches document.readyState === 'complete'.
             */
            protected function waitForPendingNavigation(float $timeoutSeconds = 5.0): void
            {
                $deadline = microtime(true) + $timeoutSeconds;

                while (microtime(true) < $deadline) {
                    try {
                        $ready = $this->driver->executeScript(
                            "return typeof window.__duskPreClick === 'undefined' && document.readyState === 'complete';"
                        );
                        if ($ready === true) {
                            return;
                        }
                    } catch (Throwable) {
                        // Page is unloading/navigating; keep polling.
                    }

                    usleep(50000);
                }
            }
        };

        if (! $this->shouldStartMaximized()) {
            $browser->resize(1920, 1080);
        }

        return $browser;
    }
}