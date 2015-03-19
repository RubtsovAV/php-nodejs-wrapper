<?php

namespace Kurbits\JavaScript\Test;


use Kurbits\JavaScript\NodeRunner;

class NodeRunnerTest extends \PHPUnit_Framework_TestCase {
    private $node;

    protected function setUp()
    {
        $this->node = new NodeRunner();
    }

    public function testCall() {
        $this->node->setSource('id = function(v) { return v; }');
        $this->assertEquals('bar', $this->node->call('id', 'bar'));
    }

    public function testNestedCall() {
        $this->node->setSource('a = {}; a.b = {}; a.b.id = function(v) { return v; }');
        $this->assertEquals('bar', $this->node->call('a.b.id', 'bar'));
    }

    /**
     * @dataProvider executeDataProvider
     */
    public function testExecute($source, $expected) {
        $this->assertEquals($expected, $this->node->execute($source));
    }

    public function executeDataProvider()
    {
        return [
            ["function() {}", null],
            ["0", 0],
            ["null", null],
            ["undefined", null],
            ["true", true],
            ["false", false],
            ["[1, 2]", [1, 2]],
            ["[1, function() {}]", [1, null]],
            ["'hello'", "hello"],
            ["'red yellow blue'.split(' ')", ["red", "yellow", "blue"]],
            ["{a:1,b:2}", (object)["a"=>1,"b"=>2]],
            ["{a:true,b:function (){}}", (object)["a"=>true]],
            ["'café'", "café"],
            ['"☃"', "☃"],
            ['"\u2603"', "☃"],
            ['"\\\\"', "\\"]
        ];
    }
}
