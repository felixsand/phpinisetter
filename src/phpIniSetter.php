<?php
/**
 * PhpIniSetter.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhpIniSetter;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Felix Sandström <http://github.com/felixsand>
 */
class PhpIniSetter extends Command
{
    /**
     */
    protected function configure()
    {
        $this
            ->setName('phpIni:set')
            ->setDescription('Set a php.ini')
            ->addArgument(
                'configKey',
                InputArgument::REQUIRED,
                'The key of the setting to be changed'
            )
            ->addArgument(
                'configValue',
                InputArgument::REQUIRED,
                'The value to change the setting to'
            )
            ->addOption(
                'file',
                null,
                InputOption::VALUE_REQUIRED,
                'If set, the specified php.ini file will be used instead of the loaded one.'
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getOption('file') ?: php_ini_loaded_file();
        if (!$filePath) {
            $output->writeln('<error>No php.ini file is set</error>');
            return -1;
        } elseif (! is_file($filePath)) {
            $output->writeln('<error>The specified php.ini file does not exists</error>');
            return -1;
        } elseif (! is_writable($filePath)) {
            $output->writeln('<error>You do not have permission to write to the php.ini file</error>');
            return -1;
        }

        $configKey = $input->getArgument('configKey');
        $configLine = $configKey . " = " . $input->getArgument('configValue') . "\n";

        $lines = [];
        $configSet = false;
        foreach (file($filePath) as $line) {
            if (strtolower(substr(trim($line), 0, strlen($configKey))) == strtolower($configKey)) {
                $line = $configLine;
                $configSet = true;
            }
            $lines[] = $line;
        }

        if (!$configSet) {
            $lines[] = $configLine;
        }

        if (file_put_contents($filePath, implode($lines))) {
            $output->writeln('<info>The php.ini file has been updated</info>');
        } else {
            $output->writeln('<error>Could not update the php.ini file</error>');
            return -1;
        }

        return 0;
    }
}
