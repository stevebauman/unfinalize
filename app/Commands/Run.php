<?php

namespace App\Commands;

use Exception;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use PhpCsFixer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class Run extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'run {paths?*} {--dry} {--annotate=} {--properties=} {--methods=} {--dir=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Remove final keywords from the configured list of vendor packages.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $paths = $this->argument('paths') ?: $this->getComposerPaths();

        if (is_int($paths)) {
            return $paths;
        }

        $tempConfigFile = $this->makeTempConfigFile($paths);

        try {
            // Run the PHP-CS-Fixer command.
            $application = new Application();

            $application->setAutoExit(false);

            $code = $application->run(new ArrayInput([
                'command' => 'fix',
                '--config' => $tempConfigFile,
                '--dry-run' => $this->option('dry'),
            ]), $output = new BufferedOutput());

            // Display the output.
            echo $output->fetch();
        } catch (Exception) {
            unlink($tempConfigFile);
        }

        return $code ?? static::FAILURE;
    }

    /**
     * Get the composer defined paths to unfinalize.
     */
    protected function getComposerPaths(): int|array
    {
        $dir = $this->option('dir') ?? getcwd();

        $composerFilePath = $dir . DIRECTORY_SEPARATOR . 'composer.json';

        if (! file_exists($composerFilePath)) {
            $this->error("composer.json is not present in directory [$dir].");

            return static::FAILURE;
        }

        $composer = json_decode(file_get_contents($composerFilePath), true);

        if (is_null($composer)) {
            $this->error('composer.json contains invalid JSON.');

            return static::FAILURE;
        }

        $packages = $composer['unfinalize'] ?? [];

        if (empty($packages)) {
            $this->info('composer.json does not contain any configured vendor paths to unfinalize.');

            return static::SUCCESS;
        }

        $dir = Str::endsWith($dir, DIRECTORY_SEPARATOR)
            ? Str::beforeLast($dir, DIRECTORY_SEPARATOR)
            : $dir;

        return array_map(fn ($package) => (
            implode(DIRECTORY_SEPARATOR, [$dir, 'vendor', $package])
        ), $packages);
    }

    /**
     * Make a temporary PHP CS Fixer config file to launch with.
     */
    protected function makeTempConfigFile(array $paths): string
    {
        $rules = array_filter([
            'Unfinalize/remove_final_keyword' => ['annotate' => $this->option('annotate')],
            'Unfinalize/change_method_visibility' => array_filter(['visibility' => $this->option('methods')]),
            'Unfinalize/change_property_visibility' => array_filter(['visibility' => $this->option('properties')]),
        ]);

        $dirs = array_map(fn (string $path) => (
            is_file($path) ? dirname($path) : $path
        ), $paths);

        $files = array_filter($paths, fn (string $path) => (
            is_file($path)
        )) ?: ['*.php'];

        $php = sprintf(<<<'PHP'
            $finder = PhpCsFixer\Finder::create()
                ->in(%s)
                ->name(%s);

            return (new PhpCsFixer\Config)
                ->setRules(%s)
                ->setFinder($finder)
                ->setUsingCache(false)
                ->setRiskyAllowed(true)
                ->registerCustomFixers([
                    new \App\RemoveFinalKeywordFixer(),
                    new \App\ChangeMethodVisibilityFixer(),
                    new \App\ChangePropertyVisibilityFixer(),
                ]);
        PHP, var_export($dirs, true), var_export($files, true), var_export($rules, true));

        // Save the configuration to a temporary file.
        $tempConfigFile = tempnam(sys_get_temp_dir(), 'php_cs_fixer_');

        file_put_contents($tempConfigFile, '<?php  ' . $php);

        return $tempConfigFile;
    }

    /**
     * Write a string as standard output.
     */
    public function line($string, $style = null, $verbosity = null): void
    {
        parent::line("[Unfinalize] - ".$string, $style, $verbosity);
    }
}
