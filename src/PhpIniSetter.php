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
        if (!($filePath = $this->getFilePath($input))) {
            $output->writeln('<error>The specified php.ini file does not exist or is not writeable</error>');
            return -1;
        }

        $configKey = $input->getArgument('configKey');
        $configLine = $configKey . " = " . $input->getArgument('configValue') . "\n";

        $lines = [];
        $configSet = false;
        foreach (file($filePath) as $line) {
            if ($this->isLineSpecifiedConfigLine($line, $configKey)) {
                $line = $configLine;
                $configSet = true;
            }
            $lines[] = $line;
        }

        if (!$configSet) {
            $lines[] = $configLine;
        }

        file_put_contents($filePath, implode($lines));
        $output->writeln('<info>The php.ini file has been updated</info>');

        return 0;
    }
    
    /**
     * @param string $line
     * @param string $configKey
     * @return bool
     */
    protected function isLineSpecifiedConfigLine($line, $configKey)
    {
        $line = trim($line);
        if (substr($line, 0, 1) == ';') {
            return false;
        }
        $configKeyLine = strtolower($configKey . '=');
        $line = str_replace(' ', '', $line) . '=';
        $configLineKeyPart = strtolower(substr($line, 0, strlen($configKeyLine)));
        
        return $configLineKeyPart == $configKeyLine;
    }
    
    /**
     * @param InputInterface $input
     * @return string
     */
    protected function getFilePath(InputInterface $input)
    {
        $filePath = $input->getOption('file') ?: php_ini_loaded_file();
        
        return (!is_file($filePath) || !is_writable($filePath)) ? '' : $filePath;
    }
}
