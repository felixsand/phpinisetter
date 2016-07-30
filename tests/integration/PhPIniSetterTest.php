<?php
/**
 * PhPIniSetter.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhpIniSetter;

use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Felix Sandström <http://github.com/felixsand>
 */
class PhPIniSetterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $phpIniTestFile;

    /**
     * @var string
     */
    private $phpIniTestContent;

    /**
     */
    public function setUp()
    {
        $this->phpIniTestFile = tempnam(sys_get_temp_dir(), 'phpIniSetterTestFile');
        $this->phpIniTestContent = <<<INI_EOF
[PHP]

bogus_config_one = On
; A comment that should not be changed
; bogus_config_in_comment = On

[Section]
; More comments that should not be changed
section.bogus_config = Off

INI_EOF;
        if (!file_put_contents($this->phpIniTestFile, $this->phpIniTestContent)) {
            throw new \RuntimeException('Could not write temporary file for Integration test');
        }
    }

    /**
     * @covers PhpIniSetter\PhpIniSetter::configure
     * @covers PhpIniSetter\PhpIniSetter::execute
     */
    public function testPhpIniSetting()
    {
        $this->assertEquals($this->phpIniTestContent, file_get_contents($this->phpIniTestFile));

        $command = new PhpIniSetter();
        $commandTester = new CommandTester($command);

        $this->assertEquals(0, $commandTester->execute([
            'configKey' => 'bogus_config_one',
            'configValue' => 'Off',
            '--file' => $this->phpIniTestFile,
        ]));

        $this->assertEquals(0, $commandTester->execute([
            'configKey' => 'section.bogus_config',
            'configValue' => 'On',
            '--file' => $this->phpIniTestFile,
        ]));

        $this->assertEquals(0, $commandTester->execute([
            'configKey' => 'bogus_config_in_comment',
            'configValue' => 'Off',
            '--file' => $this->phpIniTestFile,
        ]));

        $expectedPhpIniContent = <<<INI_EOF
[PHP]

bogus_config_one = Off
; A comment that should not be changed
; bogus_config_in_comment = On

[Section]
; More comments that should not be changed
section.bogus_config = On
bogus_config_in_comment = Off

INI_EOF;

        $this->assertEquals($expectedPhpIniContent, file_get_contents($this->phpIniTestFile));
    }

    /**
     * @covers PhpIniSetter\PhpIniSetter::configure
     * @covers PhpIniSetter\PhpIniSetter::execute
     */
    public function testRequiredInputs()
    {
        $command = new PhpIniSetter();
        $commandTester = new CommandTester($command);

        $this->assertEquals(-1, $commandTester->execute([
            'configKey' => '',
            'configValue' => 'Off',
            '--file' => $this->phpIniTestFile,
        ]));

        $this->assertEquals(-1, $commandTester->execute([
            'configKey' => 'bogus_config_one',
            'configValue' => '',
            '--file' => $this->phpIniTestFile,
        ]));
    }

    /**
     * @covers PhpIniSetter\PhpIniSetter::configure
     * @covers PhpIniSetter\PhpIniSetter::execute
     */
    public function testNonExistingIniFile()
    {
        $command = new PhpIniSetter();
        $commandTester = new CommandTester($command);
        $this->assertEquals(-1, $commandTester->execute([
            'configKey' => 'bogus_config_one',
            'configValue' => 'Off',
            '--file' => dirname(__FILE__) . '/thisFileShouldNotExist',
        ]));
    }

    /**
     * @covers PhpIniSetter\PhpIniSetter::configure
     * @covers PhpIniSetter\PhpIniSetter::execute
     */
    public function testNonWriteableFile()
    {
        chmod($this->phpIniTestFile, 0);

        $command = new PhpIniSetter();
        $commandTester = new CommandTester($command);
        $this->assertEquals(-1, $commandTester->execute([
            'configKey' => 'bogus_config_one',
            'configValue' => 'Off',
            '--file' => $this->phpIniTestFile,
        ]));

        chmod($this->phpIniTestFile, 0600);
    }

    /**
     * @covers PhpIniSetter\PhpIniSetter::configure
     * @covers PhpIniSetter\PhpIniSetter::execute
     */
    public function testNoContentWritten()
    {
        $tmpFile = '/dev/null';
        $command = new PhpIniSetter();
        $commandTester = new CommandTester($command);
        $this->assertEquals(-1, $commandTester->execute([
            'configKey' => 'bogus_config_one',
            'configValue' => 'Off',
            '--file' => $tmpFile,
        ]));
    }

    /**
     */
    public function tearDown()
    {
        unset($this->phpIniTestFile);
    }
}
