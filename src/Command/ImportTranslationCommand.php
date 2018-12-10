<?php

namespace GSS\Command;

use DirectoryIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ImportTranslationCommand extends Command implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    private $languageConfig = [];

    /**
     * @param ContainerInterface $container
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->languageConfig = $container->getParameter('language')['mapping'];
        $this->container = $container;
    }

    /**
     * @author Soner Sayakci <***REMOVED***>
     */
    protected function configure()
    {
        $this
            ->setName('gs:translation:import')
            ->setDescription('Importing translations from ini files');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $snippetsPath = $this->container->getParameter('kernel.project_dir') . '/snippets/';

        $this->clearTranslations();

        $iterator = new DirectoryIterator($snippetsPath);

        $count = 0;
        foreach ($iterator as $item) {
            if ($item->isFile()) {
                ++$count;
            }
        }

        $progress = new ProgressBar($output, $count);

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            $namespace = \str_replace('.ini', '', $fileInfo->getBasename());
            $data = \parse_ini_file($snippetsPath . $fileInfo->getFilename(), true);

            foreach ($data['en'] as $key => $_) {
                $basicData = [
                    'name' => $key,
                    'namespace' => $namespace,
                ];

                foreach ($this->languageConfig as $language) {
                    $basicData[$language] = $data[$language][$key];
                }

                $this->container->get('doctrine.dbal.default_connection')->insert('translation', $basicData);
            }

            $progress->advance();
        }

        $progress->finish();

        $output->writeln("\n");

        $output
            ->writeln('Translations imported');
        $output
            ->writeln('Clearing Translation Cache');

        $this->container->get('translation')->generateTranslationCaches();
    }

    /**
     * @author Soner Sayakci <***REMOVED***>
     */
    private function clearTranslations()
    {
        $this->container->get('doctrine.dbal.default_connection')->executeQuery('TRUNCATE TABLE translation');
    }
}
