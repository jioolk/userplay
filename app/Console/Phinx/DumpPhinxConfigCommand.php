<?php declare(strict_types=1);

/**
 * @author: Jiri Sosolik
 */

namespace UserPlay\Console\Phinx;

use Nette\DI\Container;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use UserPlay\Console\BaseCommand;

#[AsCommand(
    name: 'phinx:dump-phinx-config',
    description: 'Phinx has no native Nette integration, lets integrate it via config file dump based on .neon settings we have'
)]
class DumpPhinxConfigCommand extends BaseCommand
{
    private Container $container;

    private const OUT_PATH = 'out-path';
    private const OUT_FILE = 'out-file';

    public function __construct( Container $container )
    {
        parent::__construct();
        $this
            ->setName('phinx:dump-phinx-config')
            ->addOption(self::OUT_PATH, null, InputOption::VALUE_REQUIRED, 'What dir shall we output the file', 'temp')
            ->addOption(self::OUT_FILE, null, InputOption::VALUE_REQUIRED, 'Configuration file name itself', 'phinx.json');

        $this->container = $container;
    }

    /**
     * @see parent::validateInput()
     */
    protected function validateInput(): array
    {
        return $this->autoValidateInput([
            self::OUT_PATH => 'directory',
            self::OUT_FILE => 'pattern:[a-zA-Z0-9]+\.json',
        ]);
    }

    /**
     * @see parent::performCommand()
     */
    protected function performCommand(): int
    {
        $outDir  = (string) $this->input->getOption(self::OUT_PATH);
        $outFile = (string) $this->input->getOption(self::OUT_FILE);

        $phConfig    = $this->container->getParameter('phinx');
        $phConfigStr = \json_encode($phConfig, JSON_PRETTY_PRINT);
        if ( $phConfigStr === false ) {
            $this->output->writeln('<error>Could not encode JSON</error>');
            return 5;
        }

        $outComplete = FileSystem::joinPaths($outDir, $outFile);
        FileSystem::write($outComplete, $phConfigStr);

        $this->output->writeln("Phinx configuration file has been created at $outComplete [<info>OK</info>]");

        return 0;
    }
}