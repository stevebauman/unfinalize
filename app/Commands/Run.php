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
    protected $signature = 'run {--dry} {--mark-internal}';

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
        $composerFilePath = ($dir = getcwd()) . DIRECTORY_SEPARATOR . 'composer.json';

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

        $tempConfigFile = $this->makeTempConfigFile($dir, $packages);

        try {
            // Run the PHP-CS-Fixer command
            $application = new Application();

            $application->setAutoExit(false);

            $application->run(new ArrayInput([
                'command' => 'fix',
                '--config' => $tempConfigFile,
                '--dry-run' => $this->option('dry'),
            ]), $output = new BufferedOutput());

            // Display the output
            echo $output->fetch();
        } catch (Exception) {
            unlink($tempConfigFile);
        }

        return static::SUCCESS;
    }

    /**
     * Make a temporary PHP CS Fixer config file to launch with.
     */
    protected function makeTempConfigFile(string $dir, array $packages): string
    {
        $dirs = array_map(fn ($package) => (
            Str::wrap(implode(DIRECTORY_SEPARATOR, [$dir, 'vendor', $package]), "'")
        ), $packages);

        $php = sprintf(<<<'PHP'
            $finder = PhpCsFixer\Finder::create()
                ->in([%s])
                ->name('*.php');

            return (new PhpCsFixer\Config)
                ->setFinder($finder)
                ->setUsingCache(false)
                ->setRiskyAllowed(true)
                ->setRules(['Unfinalize/remove_final_keyword' => ['mark_internal' => %s]])
                ->registerCustomFixers([new \App\RemoveFinalKeywordFixer()]);
        PHP, implode(',', $dirs), var_export($this->option('mark-internal'), true));

        // Save the configuration to a temporary file.
        $tempConfigFile = tempnam(sys_get_temp_dir(), 'php_cs_fixer_');

        file_put_contents($tempConfigFile, '<?php  ' . $php);

        return $tempConfigFile;
    }

    /**
     * Write a string as standard output.
     */
    public function line($string, $style = null, $verbosity = null)
    {
        parent::line("[Unfinalize] - ".$string, $style, $verbosity);
    }
}
