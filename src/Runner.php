<?php

namespace Kurbits\JavaScript;

interface Runner
{
    public static function compile($source);

    /**
     * @return array
     */
    public function getSources();

    /**
     * @param array $sources
     */
    public function setSources(array $sources = []);

    /**
     * @param string $source
     */
    public function setSource($source);

    /**
     * Executes a string of JavaScript and returns the result if available.
     *
     * @param $source
     * @return mixed
     */
    public function execute($source);

    /**
     * Invokes a JavaScript function with the specified arguments.
     *
     * @param $identifier The function identifier.
     * @param ...$params The arguments to pass to the function.
     * @return mixed
     */
    public function call($identifier, ...$params);
}