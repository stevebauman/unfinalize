<?php

namespace App\Commands;

use App\NodeVisitor\RemoveFinalKeywordVisitor;
use LaravelZero\Framework\Commands\Command;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

class Run extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'run {--dry} {--mark-final} {--mark-readonly} {--dir=}';

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

        $this->info('Unfinalizing the following packages: ' . implode(', ', $packages));


        $lexer = new Emulative([
            'usedAttributes' => [
                'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos',
            ],
        ]);

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7, $lexer);

        $prettyPrinter = new Standard();

        $nodeTraverser = new NodeTraverser();

        $nodeTraverser->addVisitor(new NameResolver());
        $nodeTraverser->addVisitor(new CloningVisitor());
        $nodeTraverser->addVisitor(new RemoveFinalKeywordVisitor(
            $this->option('mark-final') ?? false,
            $this->option('mark-readonly') ?? false
        ));

        foreach($packages as $package)
        {
            $packagePath = $dir . '/vendor/' . $package;
            if (! file_exists($packagePath)) {
                $this->error("Package [$package] does not exist in [$packagePath].");
                return static::FAILURE;
            }

            $recursiveIteratorIterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($packagePath)
            );

            $modifiedFiles = [];
            /** @var SplFileInfo $file */
            foreach($recursiveIteratorIterator as $file){
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                if (! $file->isFile()) {
                    continue;
                }

                if ($file->isLink()) {
                    continue;
                }

                $filePath = $file->getPathname();

                $contents = file_get_contents($filePath);

                if ($contents === false) {
                    throw new RuntimeException('Unable to read file ['.$filePath.'].');
                }

                $stmts = $parser->parse($contents);

                $code = $prettyPrinter->printFormatPreserving(
                    $nodeTraverser->traverse($stmts),
                    $stmts,
                    $lexer->getTokens()
                );

                if ($code === $contents) {
                    continue;
                }

                $result = file_put_contents($filePath, $code);
                if ($result === false) {
                    throw new RuntimeException('Unable to write file ['.$filePath.'].');
                }

                $modifiedFiles[] = $filePath;
            }
        }

        foreach($modifiedFiles as $modifiedFile) {
            $this->info("Modified [$modifiedFile].");
        }

        return static::SUCCESS;
    }

    /**
     * Write a string as standard output.
     */
    public function line($string, $style = null, $verbosity = null): void
    {
        parent::line("[Unfinalize] - ".$string, $style, $verbosity);
    }
}
