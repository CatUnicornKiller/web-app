<?php

namespace App\Console;

use Doctrine\DBAL;
use Nette\Utils\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zenify\DoctrineFixtures\Contract\Alice\AliceLoaderInterface;

/**
 * Fill in database with initial data. Actual data are stored in YAML files
 * in fixtures directory. Also, 'db:fill' command is registered to provide
 * convenient usage of this function.
 */
class DoctrineFixtures extends Command
{

    /** @var AliceLoaderInterface */
    private $aliceLoader;
    /** @var DBAL\Connection */
    private $dbConnection;

    /**
     * DI Constructor.
     * @param AliceLoaderInterface $aliceLoader
     * @param DBAL\Connection $dbConnection
     */
    public function __construct(AliceLoaderInterface $aliceLoader, DBAL\Connection $dbConnection)
    {
        parent::__construct();
        $this->aliceLoader = $aliceLoader;
        $this->dbConnection = $dbConnection;
    }

    /**
     * Register the 'db:fill' command in the framework
     */
    protected function configure()
    {
        $this->setName('db:fill')->setDescription('Fill database with initial data.');
    }

    /**
     * Execute the database filling.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int 0 on success, 1 on error
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fixtureDir = __DIR__ . '/../../fixtures';
        $fixtureFiles = [];

        foreach (Finder::findFiles("*.neon", "*.yml", "*.yaml")->in($fixtureDir) as $file) {
            $fixtureFiles[] = $file->getRealPath();
        }

        sort($fixtureFiles);
        $this->aliceLoader->load($fixtureFiles);

        $output->writeln('<info>[OK] - DB:FILL</info>');
        return 0;
    }
}
