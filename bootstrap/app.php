<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    realpath(__DIR__.'/../')
);

/*
 * |--------------------------------------------------------------------------
 * | Detect The Application Environment
 * |--------------------------------------------------------------------------
 * |
 * | Laravel takes a dead simple approach to your application environments
 * | so you can just specify a machine name for the host that matches a
 * | given environment, then we will automatically detect it for you.
 * |
 */
$env = $app->detectEnvironment ( function () {
	if (getenv ( 'APP_ENV' ) && file_exists ( __DIR__ . '/../.env.' . getenv ( 'APP_ENV' ) )) {
		$envFile = '.env.' . getenv ( 'APP_ENV' );
	} else {
		$envFile = '.env.local';
	}
	$dotenv = new Dotenv\Dotenv(__DIR__ . '/../', $envFile);
	$dotenv->overload();
});

$environment = $app->environment();

if (!is_null($environment)) {
	putenv( "APP_ENV={$environment}" );
	if (getenv ( 'APP_ENV' ) && file_exists ( __DIR__ . '/../.env.' . getenv ( 'APP_ENV' ) )) {
		$envFile = '.env.' . getenv ( 'APP_ENV' );
	} else {
		$envFile = '.env.production';
	}
	$dotenv = new Dotenv\Dotenv(__DIR__ . '/../', $envFile);
	$dotenv->overload();
}


/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
