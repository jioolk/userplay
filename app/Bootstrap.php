<?php declare(strict_types=1);

namespace UserPlay;

use Nette;
use Nette\Bootstrap\Configurator;


class Bootstrap
{
    private Configurator $configurator;
    private string $rootDir;


    public function __construct()
    {
        $this->rootDir = \dirname(__DIR__ . '../' );

        $this->configurator = new Configurator;
        $this->configurator->setTempDirectory($this->rootDir . '/temp');
    }


    public function bootWebApplication(): Nette\DI\Container
    {
        $this->initializeEnvironment();
        $this->setupContainer([
            'parameters.neon',
            'common.neon',
            'www/www.neon',
        ]);

        return $this->configurator->createContainer();
    }

    public function bootCLIApplication(): Nette\DI\Container
    {
        $this->initializeEnvironment();
        $this->setupContainer([
            'parameters.neon',
            'common.neon',
            'cli/cli.neon',
        ]);

        return $this->configurator->createContainer();
    }

    public function initializeEnvironment(): void
    {
        //$this->configurator->setDebugMode('secret@23.75.345.200'); // enable for your remote IP
        $this->configurator->enableTracy($this->rootDir . '/log');

        $this->configurator->createRobotLoader()
        ->addDirectory(__DIR__)
        ->register();
    }

    /**
     * @param string[] $loadConfigs path to config file after <projectRoot>/config/
     * @return void
     */
    private function setupContainer( array $loadConfigs = [] ): void
    {
        // I want all envs available directly via nette/di in .neons
        $envs = \getenv();
        $this->configurator->addDynamicParameters([
            'ENV' => $envs,
        ]);

        $configDir = $this->rootDir . '/config';
        foreach ( $loadConfigs as $configToLoad ) {
            $this->configurator->addConfig($configDir . '/' . $configToLoad);
        }

        $env = \getenv('APP_ENV');
        if ( $env !== 'production' ) {
            $this->configurator->setDebugMode(true);
        }
    }
}
