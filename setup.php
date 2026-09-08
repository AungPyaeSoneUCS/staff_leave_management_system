<?php

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('This script must be run from the command line.');
}

$colors = [
    'reset' => "\033[0m",
    'green' => "\033[32m",
    'red' => "\033[31m",
    'yellow' => "\033[33m",
    'blue' => "\033[34m",
    'cyan' => "\033[36m",
];

function info(string $message): void
{
    global $colors;
    echo $colors['cyan'].'[INFO]'.$colors['reset'].' '.$message.PHP_EOL;
}

function success(string $message): void
{
    global $colors;
    echo $colors['green'].'[ OK ]'.$colors['reset'].' '.$message.PHP_EOL;
}

function warn(string $message): void
{
    global $colors;
    echo $colors['yellow'].'[WARN]'.$colors['reset'].' '.$message.PHP_EOL;
}

function error(string $message): void
{
    global $colors;
    echo $colors['red'].'[FAIL]'.$colors['reset'].' '.$message.PHP_EOL;
}

function command(string $cmd, bool $required = true): int
{
    info("Running: {$cmd}");
    passthru($cmd.' 2>&1', $exitCode);
    if ($exitCode !== 0 && $required) {
        error("Command failed with exit code {$exitCode}: {$cmd}");
    }

    return $exitCode;
}

function fileContains(string $file, string $needle): bool
{
    if (! file_exists($file)) {
        return false;
    }

    return str_contains(file_get_contents($file), $needle);
}

function migrationExists(string $migration): bool
{
    $files = glob(database_path('migrations/*'.$migration.'.php'));

    return ! empty($files);
}

function database_path(string $path = ''): string
{
    return __DIR__.'/database/'.ltrim($path, '/');
}

function base_path(string $path = ''): string
{
    return __DIR__.'/'.ltrim($path, '/');
}

$errors = 0;

info('Starting CU Leave setup...');
echo PHP_EOL;

$phpVersion = PHP_VERSION;
if (version_compare($phpVersion, '8.3.0', '<')) {
    error("PHP 8.3+ is required. Found: {$phpVersion}");
    $errors++;
} else {
    success("PHP version {$phpVersion}");
}

$requiredExtensions = [
    'pdo_mysql',
    'mbstring',
    'tokenizer',
    'xml',
    'ctype',
    'json',
    'bcmath',
    'fileinfo',
    'gd',
];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        success("Extension loaded: {$ext}");
    } else {
        error("Missing required extension: {$ext}");
        $errors++;
    }
}

if ($errors > 0) {
    echo PHP_EOL.$colors['red'].'Fix the above errors before continuing.'.$colors['reset'].PHP_EOL;
    exit(1);
}

echo PHP_EOL;

info('Checking Composer...');
$composer = PHP_OS_FAMILY === 'Windows' ? 'composer.bat' : 'composer';
if (shell_exec("which {$composer} 2>/dev/null") || shell_exec("where {$composer} 2>/dev/null")) {
    success('Composer is available');
} else {
    warn('Composer not found in PATH. You may need to run composer commands manually.');
}

info('Checking Node/NPM...');
$node = shell_exec('node --version 2>/dev/null');
$npm = shell_exec('npm --version 2>/dev/null');
if ($node && $npm) {
    success("Node {$node}NPM {$npm}");
} else {
    warn('Node/NPM not found. Frontend build will be skipped.');
}

echo PHP_EOL;

info('Checking .env file...');
if (! file_exists(base_path('.env'))) {
    if (file_exists(base_path('.env.example'))) {
        copy(base_path('.env.example'), base_path('.env'));
        success('Created .env from .env.example');
    } else {
        error('.env.example not found. Cannot create .env file.');
        exit(1);
    }
} else {
    success('.env already exists');
}

info('Generating application key...');
$keyOutput = shell_exec('php artisan key:generate --ansi 2>&1');
success($keyOutput);

if (shell_exec("which {$composer} 2>/dev/null") || shell_exec("where {$composer} 2>/dev/null")) {
    info('Installing Composer dependencies...');
    $exitCode = command("{$composer} install --no-interaction --prefer-dist");
    if ($exitCode !== 0) {
        error('Composer install failed');
        exit(1);
    }
    success('Composer dependencies installed');
} else {
    warn('Skipping composer install (composer not found)');
}

echo PHP_EOL;

if ($node && $npm) {
    info('Installing NPM dependencies...');
    command('npm install --ignore-scripts');

    info('Building frontend assets...');
    command('npm run build');
} else {
    warn('Skipping NPM install/build (Node/NPM not found)');
}

echo PHP_EOL;

info('Creating storage directories...');
$dirs = [
    base_path('storage/app'),
    base_path('storage/framework'),
    base_path('storage/framework/cache'),
    base_path('storage/framework/sessions'),
    base_path('storage/framework/views'),
    base_path('storage/logs'),
    base_path('storage/app/public/leave-attachments'),
    base_path('storage/app/public/reports'),
    base_path('public/storage'),
];

foreach ($dirs as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0775, true);
        success("Created directory: {$dir}");
    } else {
        success("Directory exists: {$dir}");
    }
}

info('Checking queue tables...');
$queueCfg = parse_ini_file(base_path('.env'));
$queueDriver = $queueCfg['QUEUE_CONNECTION'] ?? 'database';
if ($queueDriver === 'database') {
    if (! migrationExists('create_failed_jobs_table') && ! migrationExists('0001_01_01_000003_create_failed_jobs_table')) {
        info('Publishing queue migrations...');
        command('php artisan queue:table --ansi');
        command('php artisan optimize:clear --ansi');
        success('Failed jobs migration created');
    } else {
        success('Failed jobs migration already exists');
    }
}

$sessionCfg = parse_ini_file(base_path('.env'));
$sessionDriver = $sessionCfg['SESSION_DRIVER'] ?? 'file';
if ($sessionDriver === 'database') {
    if (! migrationExists('create_sessions_table') && ! migrationExists('0001_01_01_000004_create_sessions_table')) {
        info('Publishing session migration...');
        command('php artisan session:table --ansi');
        command('php artisan optimize:clear --ansi');
        success('Session migration created');
    } else {
        success('Session migration already exists');
    }
}

echo PHP_EOL;

info('Running database migrations...');
command('php artisan migrate --force --ansi');

echo PHP_EOL;

info('Seeding database...');
command('php artisan db:seed --force --ansi');

echo PHP_EOL;

info('Creating storage symlink...');
command('php artisan storage:link --force --ansi');

echo PHP_EOL;

info('Clearing caches...');
command('php artisan config:clear --ansi');
command('php artisan route:clear --ansi');
command('php artisan view:clear --ansi');
command('php artisan cache:clear --ansi');

echo PHP_EOL;

success('Setup completed!');
echo PHP_EOL;
info('Next steps:');
echo '  1. Verify database credentials in .env'.PHP_EOL;
echo '  2. Start queue worker: php artisan queue:work --tries=1 --timeout=0'.PHP_EOL;
echo '  3. Start dev server: php artisan serve'.PHP_EOL;
echo '  4. Start Vite dev server: npm run dev'.PHP_EOL;
echo '  5. Run tests: php artisan test'.PHP_EOL;
echo PHP_EOL;
info('Default login credentials (from UserSeeder):');
echo '  Admin: admin@gmail.com / password'.PHP_EOL;
echo '  Department Head (IT): sarah.johnson@gmail.com / password'.PHP_EOL;
echo '  Department Head (HR): michael.brown@gmail.com / password'.PHP_EOL;
echo '  Staff (IT): john.smith@gmail.com / password'.PHP_EOL;
echo PHP_EOL;
