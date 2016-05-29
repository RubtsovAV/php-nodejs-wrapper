<?php

namespace Kurbits\JavaScript;

use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

abstract class ExternalRunner implements Runner
{
    /**
     * @var ProcessBuilder
     */
    protected $builder;

    protected $sources;

    protected $commands;

    public function __construct($source = null)
    {
        $bin = $this->findExecutable($this->commands);
        $this->builder = new ProcessBuilder();
        $this->builder->setPrefix($bin);

        if (!is_null($source)) {
            $this->setSource($source);
        }
    }

    public static function compile($source)
    {
        $node = new NodeRunner();
        $node->setSource($source);
        return $node;
    }

    /**
     * @return array
     */
    public function getSources()
    {
        return $this->sources;
    }

    /**
     * @param array $sources
     */
    public function setSources(array $sources = [])
    {
        $this->sources = $sources;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->sources = [$source];
    }
    
    /**
     * @param string $source
     */
    public function addSource($source)
    {
        $this->sources[] = $source;
    }

    /**
     * Executes a string of JavaScript and returns the result if available.
     *
     * @param string $source
     * @return mixed
     */
    public function execute($source = '')
    {
        $source = $this->encode($source);

        $script = $this->compileSource($source);

        $output = $this->evaluateScript($script);

        return $this->extractResult($output);
    }

    /**
     * Invokes a JavaScript function with the specified arguments.
     *
     * @param $identifier The function identifier.
     * @param ...$params The arguments to pass to the function.
     * @return mixed
     */
    public function call($identifier, ...$params)
    {
        $params = json_encode($params);
        return $this->execute("{$identifier}.apply(this, {$params})");
    }

    /**
     * Wraps the source in the runner and returns it.
     *
     * @param $source
     * @return mixed
     */
    protected function compileSource($source)
    {
        //$source = mb_convert_encoding($source, '8bit', mb_detect_encoding($source));

        $source = $this->prepareSource($source);

        $runner = file_get_contents(__DIR__ . '/support/node_runner.js');

        if (!empty($this->sources)) {
            $sources = implode("\n;", $this->sources);
            $source = "{$sources};\n{$source}";
        }

        $source = str_replace('//--SOURCE--//', $source, $runner);
        return $source;
    }

    /**
     * Extracts the result from the Node command output.
     *
     * @param $output
     * @return mixed
     * @throws RuntimeException
     */
    protected function extractResult($output)
    {
        list($status, $value) = !empty($output) ? array_pad(json_decode($output), 2, null) : [null, null];

        if ($status === 'ok') {
            return $value;
        } elseif (preg_match('/SyntaxError:/', $value) === 1) {
            throw new RuntimeException($value);
        } else {
            throw new RuntimeException($value);
        }
    }

    /**
     * Finds the Node executable.
     *
     * @param array $commands List of commands to look for.
     * @return string Full path to the executable.
     * @throws RuntimeException
     */
    protected function findExecutable($commands = [])
    {
        $finder = new ExecutableFinder();

        foreach ($commands as $command) {
            $bin = $finder->find($command);
            if (!empty($bin)) return $bin;
        }

        throw new RuntimeException('Node could not be found.');
    }

    /**
     * Evaluate the script in Node and return the output.
     *
     * @param $script
     * @return string
     */
    protected function evaluateScript($script)
    {
        $process = $this->getProcess('-e', $script);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $output = $process->getOutput();
        return $output;
    }

    /**
     * Get a Node process tu run.
     *
     * @param $args
     * @return Process
     */
    protected function getProcess(...$args)
    {
        $process = $this->builder
            ->setArguments($args)
            ->getProcess();

        return $process;
    }

    /**
     * @param $source
     * @return string
     */
    protected function prepareSource($source)
    {
        if (empty($source)) {
            return '';
        }
        $source = utf8_encode($source);
        $source = json_encode("({$source})");
        $source = "return eval({$source})";
        return $source;
    }


    protected function encode($source)
    {
        if (($detectedEncoding = mb_detect_encoding($source, mb_detect_order(), true)) !== 'UTF-8') {
            return mb_convert_encoding($source, 'UTF-8', $detectedEncoding);
        }

        $source = $this->encodeUnicodeCodepoints($source);

        return $source;
    }

    protected function encodeUnicodeCodepoints($source)
    {
        preg_match_all('/[\x{0080}-\x{ffff}]/u', $source, $matches);

        foreach ($matches[0] as $char) {
            list(, $code) = unpack('n', mb_convert_encoding($char, 'UCS-2BE', 'UTF-8'));
            $code = sprintf('\u%04X', $code);
            $source = preg_replace("/$char/u", $code, $source);
        }

        return $source;
    }
}
