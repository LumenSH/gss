<?php

namespace GSS\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ExportTranslationCommand extends Command implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    private $exportedLanguages;

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->exportedLanguages = $container->getParameter('language')['mapping'];
        $this->container = $container;
    }

    /**
     * @author Soner Sayakci <***REMOVED***>
     */
    protected function configure()
    {
        $this
            ->setName('gs:translation:export')
            ->setDescription('Exporting the current translation from the database to ini files');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $translations = $this->getTranslations();
        $snipetsPfad = $this->container->getParameter('kernel.project_dir') . '/snippets/';

        foreach ($translations as $namespace => $translation) {
            $namespaceFile = $snipetsPfad . $namespace . '.ini';
            @\mkdir(\dirname($namespaceFile), 0777, true);

            if (\file_exists($namespaceFile)) {
                \unlink($namespaceFile);
            }

            foreach ($this->exportedLanguages as $item) {
                \file_put_contents($namespaceFile, '[' . $item . ']' . "\n", FILE_APPEND);

                foreach ($translation[$item] as $name => $enValue) {
                    \file_put_contents($namespaceFile, $name . '="' . $enValue . "\"\n", FILE_APPEND);
                }
            }
        }
    }

    /**
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function getTranslations()
    {
        $dbTranslations = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT * FROM translation');
        $translations = [];

        foreach ($dbTranslations as $translation) {
            if (!isset($translations[$translation['namespace']])) {
                $translations[$translation['namespace']] = [];
                foreach ($this->exportedLanguages as $language) {
                    $translations[$translation['namespace']][$language] = [];
                }
            }

            foreach ($this->exportedLanguages as $language) {
                $translations[$translation['namespace']][$language][$translation['name']] = $translation[$language];
            }
        }

        return $translations;
    }
}
