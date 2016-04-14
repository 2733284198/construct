<?php

namespace JonathanTorres\Construct\Tests\Commands;

use Illuminate\Filesystem\Filesystem;
use JonathanTorres\Construct\Commands\ConstructCommand;
use JonathanTorres\Construct\Construct;
use JonathanTorres\Construct\Helpers\Str;
use Mockery;
use PHPUnit_Framework_TestCase as PHPUnit;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ConstructCommandTest extends PHPUnit
{
    protected $filesystem;
    protected $systemPhpVersion;

    protected function setUp()
    {
        $this->filesystem = Mockery::mock('JonathanTorres\Construct\Helpers\Filesystem');
        $this->systemPhpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
    }

    protected function tearDown()
    {
        Mockery::close();
    }

    public function testProjectGeneration()
    {
        $this->setMocks(3, 2);

        $app = $this->setApplication();
        $command = $app->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(), 'name' => 'vendor/project']);

        $this->assertSame('Project "vendor/project" constructed.' . PHP_EOL, $commandTester->getDisplay());
    }

    public function testProjectGenerationWithInvalidProjectName()
    {
        $app = $this->setApplication();
        $command = $app->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'someinvalidname',
        ]);

        $output = 'Warning: "someinvalidname" is not a valid project name, please use "vendor/project"' . PHP_EOL;

        $this->assertSame($output, $commandTester->getDisplay());
    }

    public function testProjectGenerationWithPhpInProjectName()
    {
        $this->setMocks(3, 2);

        $app = $this->setApplication();
        $command = $app->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(), 'name' => 'vendor/php-project']);

        $output = 'Warning: If you are about to create a micro-package "vendor/php-project" ' .
                  'should optimally not contain a "php" notation in the project name.' . PHP_EOL .
                  'Project "vendor/php-project" constructed.' . PHP_EOL;

        $this->assertSame($output, $commandTester->getDisplay());
    }

    public function testProjectGenerationWithUnknownLicense()
    {
        $this->setMocks(3, 2);

        $app = $this->setApplication();
        $command = $app->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'vendor/project',
            '--license' => 'noidealicense',
        ]);

        $output = 'Warning: "noidealicense" is not a supported license. Using MIT.' . PHP_EOL
            . 'Project "vendor/project" constructed.' . PHP_EOL;

        $this->assertSame($output, $commandTester->getDisplay());
    }

    public function testProjectGenerationWithUnknownTestingFramework()
    {
        $this->setMocks(3, 2);

        $app = $this->setApplication();
        $command = $app->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'vendor/project',
            '--test' => 'idontexist',
        ]);

        $output = 'Warning: "idontexist" is not a supported testing framework. Using phpunit.' . PHP_EOL .
                  'Project "vendor/project" constructed.' . PHP_EOL;

        $this->assertSame($output, $commandTester->getDisplay());
    }

    public function testProjectGenerationWithASpecifiedTestingFramework()
    {
        $this->setMocks(2, 2, 1, 8, 8);

        $app = $this->setApplication();
        $command = $app->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'vendor/project',
            '--test' => 'behat'
        ]);

        $output = 'Initialized behat.' . PHP_EOL . 'Project "vendor/project" constructed.' . PHP_EOL;
        $this->assertSame($output, $commandTester->getDisplay());
    }

    public function testProjectGenerationWithASpecifiedTestingFrameworkViaAlias()
    {
        $this->setMocks(2, 2, 1, 8, 8);

        $app = $this->setApplication();
        $command = $app->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'vendor/project',
            '--test-framework' => 'behat'
        ]);

        $output = 'Initialized behat.' . PHP_EOL . 'Project "vendor/project" constructed.' . PHP_EOL;
        $this->assertSame($output, $commandTester->getDisplay());
    }

    public function testProjectGenerationWithSpecifiedPhpVersion()
    {
        $this->setMocks(3, 2);

        $app = $this->setApplication();
        $command = $app->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'vendor/project',
            '--php' => '5.6.0'
        ]);

        $output = '';

        // show warning on php versions less than 5.6.0
        if (version_compare($this->systemPhpVersion, '5.6.0', '<')) {
            $output .= 'Warning: "5.6.0" is greater than your installed php version. Using version ' . $this->systemPhpVersion . PHP_EOL;
        }

        $output .= 'Project "vendor/project" constructed.' . PHP_EOL;

        $this->assertSame($output, $commandTester->getDisplay());
    }

    public function testProjectGenerationWithAnInvalidPhpVersion()
    {
        $this->setMocks(3, 2);

        $app = $this->setApplication();
        $command = $app->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'vendor/project',
            '--php' => 'invalid'
        ]);

        $output = 'Warning: "invalid" is not a valid php version. Using version ' . $this->systemPhpVersion . PHP_EOL .
                  'Project "vendor/project" constructed.' . PHP_EOL;
        $this->assertSame($output, $commandTester->getDisplay());
    }

    public function testProjectGenerationWithASpecifiedLicense()
    {
        $this->setMocks(3, 2);

        $app = $this->setApplication();
        $command = $app->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'vendor/project',
            '--license' => 'Apache-2.0'
        ]);

        $this->assertSame('Project "vendor/project" constructed.' . PHP_EOL, $commandTester->getDisplay());
    }

    public function testProjectGenerationWithASpecifiedNamespace()
    {
        $this->setMocks(3, 2);

        $app = $this->setApplication();
        $command = $app->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'vendor/project',
            '--namespace' => 'JonathanTorres\\MyAwesomeProject'
        ]);

        $this->assertSame('Project "vendor/project" constructed.' . PHP_EOL, $commandTester->getDisplay());
    }

    public function testProjectGenerationWithAnInitializedGithubRepo()
    {
        $this->setMocks(3, 3);

        $app = $this->setApplication();
        $command = $app->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'vendor/project',
            '--git' => true
        ]);

        $output = 'Initialized git repo in "project".' . PHP_EOL . 'Project "vendor/project" constructed.' . PHP_EOL;
        $this->assertSame($output, $commandTester->getDisplay());
    }

    public function testProjectGenerationWithPhpCs()
    {
        $this->setMocks(3, 2, 2);

        $app = $this->setApplication();
        $command = $app->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'vendor/project',
            '--phpcs' => true
        ]);

        $this->assertSame('Project "vendor/project" constructed.' . PHP_EOL, $commandTester->getDisplay());
    }

    public function testProjectGenerationWithSpecifiedComposerKeywords()
    {
        $this->setMocks(3, 2);

        $app = $this->setApplication();
        $command = $app->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'vendor/project',
            '--keywords' => 'some,project,keywords'
        ]);

        $this->assertSame('Project "vendor/project" constructed.' . PHP_EOL, $commandTester->getDisplay());
    }

    public function testProjectGenerationWithVagrant()
    {
        $this->setMocks(3, 2, 2);

        $app = $this->setApplication();
        $command = $app->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'vendor/project',
            '--vagrant' => true
        ]);

        $this->assertSame('Project "vendor/project" constructed.' . PHP_EOL, $commandTester->getDisplay());
    }

    public function testProjectGenerationWithEditorConfig()
    {
        $this->setMocks(3, 2, 2);

        $app = $this->setApplication();
        $command = $app->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'vendor/project',
            '--editor-config' => true
        ]);

        $this->assertSame('Project "vendor/project" constructed.' . PHP_EOL, $commandTester->getDisplay());
    }

    public function testProjectGenerationWithEnvironmentFiles()
    {
        $this->setMocks(3, 2, 2, 11, 11);

        $app = $this->setApplication();
        $command = $app->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'vendor/project',
            '--env' => true
        ]);

        $this->assertSame('Project "vendor/project" constructed.' . PHP_EOL, $commandTester->getDisplay());
    }

    public function testProjectGenerationWithGitHubTemplates()
    {
        $this->setMocks(4, 2, 3);
        $this->filesystem->shouldReceive('move')->times(1)->andReturnNull();

        $app = $this->setApplication();
        $command = $app->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'vendor/project',
            '--github-templates' => true
        ]);

        $this->assertSame('Project "vendor/project" constructed.' . PHP_EOL, $commandTester->getDisplay());
    }

    /**
     * @group integration
     */
    public function testExecutable()
    {
        $constructCommand = 'php construct';
        exec($constructCommand, $output, $returnValue);

        $this->assertStringStartsWith(
            'Construct',
            $output[1],
            'Expected application name not present.'
        );

        $this->assertEquals(0, $returnValue);
    }

    protected function setApplication()
    {
        $app = new Application();
        $construct = new Construct($this->filesystem, new Str());
        $app->add(new ConstructCommand($construct, new Str()));

        return $app;
    }

    /**
     * @param int $makeDirectoryTimes
     * @param int $isDirectoryTimes
     * @param int $copyTimes
     * @param int $getTimes
     * @param int $putTimes
     */
    protected function setMocks($makeDirectoryTimes = 3, $isDirectoryTimes = 1, $copyTimes = 1, $getTimes = 10, $putTimes = 10)
    {
        $this->filesystem->shouldReceive('makeDirectory')->times($makeDirectoryTimes)->andReturnNull();
        $this->filesystem->shouldReceive('isDirectory')->times($isDirectoryTimes)->andReturnNull();
        $this->filesystem->shouldReceive('copy')->times($copyTimes)->andReturnNull();
        $this->filesystem->shouldReceive('get')->times($getTimes)->andReturnNull();
        $this->filesystem->shouldReceive('put')->times($putTimes)->andReturnNull();
    }
}
