<?php
/*
 * This file is part of the Composer Grunt bridge package.
 *
 * Copyright (c) 2015 John Bloch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JPB\Composer\GruntBridge;

use Composer\Util\ProcessExecutor;
use Symfony\Component\Process\ExecutableFinder;

/**
 * A simple client for performing Grunt operations.
 */
class GruntClient
{

    /**
     * Create a new Grunt client.
     *
     * @return self The newly created client.
     */
    public static function create(): self
    {
        return new self(new ProcessExecutor(), new ExecutableFinder());
    }

    /**
     * Construct a new Grunt client.
     *
     * @param ProcessExecutor|null $processExecutor The process executor to use.
     * @param ExecutableFinder|null $executableFinder The executable finder to use.
     * @param string $getcwd The getcwd() implementation to use.
     * @param string $chdir The chdir() implementation to use.
     */
    public function __construct(
        ProcessExecutor $processExecutor,
        ExecutableFinder $executableFinder,
        $getcwd = 'getcwd',
        $chdir = 'chdir'
    )
    {
        $this->processExecutor = $processExecutor;
        $this->executableFinder = $executableFinder;
        $this->getcwd = $getcwd;
        $this->chdir = $chdir;
    }

    /**
     * Run a grunt task.
     *
     * @param string|null $task The task to run, or null for the default task.
     * @param string|null $path The path to the directory containing the Gruntfile, or null to use the current working directory.
     *
     * @throws Exception\GruntNotFoundException      If the grunt executable cannot be located.
     * @throws Exception\GruntCommandFailedException If the operation fails.
     */
    public function runTask($task = null, $path = null)
    {
        if ($task && !is_array($task)) {
            $task = [$task];
        }
        $this->executeGrunt($task ?: [], $path);
    }

    /**
     * Execute an Grunt command.
     *
     * @param             array                 [integer,string] $arguments            The arguments to pass to the grunt executable.
     * @param string|null $workingDirectoryPath The path to the working directory, or null to use the current working directory.
     *
     * @throws Exception\GruntNotFoundException      If the grunt executable cannot be located.
     * @throws Exception\GruntCommandFailedException If the operation fails.
     */
    protected function executeGrunt(
        array $arguments,
        $workingDirectoryPath = null
    )
    {
        array_unshift($arguments, $this->gruntPath());
        $command = implode(' ', array_map('escapeshellarg', $arguments));

        if (null !== $workingDirectoryPath) {
            $previousWorkingDirectoryPath = call_user_func($this->getcwd);
            call_user_func($this->chdir, $workingDirectoryPath);
        }

        $exitCode = 0;

        if ( file_exists( 'Gruntfile.js')) {
            $exitCode = $this->processExecutor->execute($command);
        }

        if (null !== $workingDirectoryPath) {
            call_user_func($this->chdir, $previousWorkingDirectoryPath);
        }

        if (0 !== $exitCode) {
            throw new Exception\GruntCommandFailedException($command);
        }
    }

    /**
     * Get the grunt executable path.
     *
     * @return string                           The path to the grunt executable.
     * @throws Exception\GruntNotFoundException If the grunt executable cannot be located.
     */
    protected function gruntPath()
    {
        if (null === $this->gruntPath) {
            $this->gruntPath = $this->executableFinder->find('grunt');
            if (null === $this->gruntPath) {
                throw new Exception\GruntNotFoundException();
            }
        }

        return $this->gruntPath;
    }

    private $processExecutor;
    private $executableFinder;
    private $getcwd;
    private $chdir;
    private $gruntPath;
}
