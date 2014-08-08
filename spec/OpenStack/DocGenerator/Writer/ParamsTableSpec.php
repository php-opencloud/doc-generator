<?php

namespace spec\OpenStack\DocGenerator\Writer;

use GuzzleHttp\Stream\StreamInterface;
use OpenStack\Common\Rest\Operation;
use OpenStack\Common\Rest\Parameter;
use OpenStack\Common\Rest\ServiceDescription;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ParamsTableSpec extends ObjectBehavior
{
    private $stream;
    private $description;

    function let(StreamInterface $stream, ServiceDescription $description)
    {
        $this->stream = $stream;

        $class  = new \ReflectionClass(__NAMESPACE__ . '\\ParamsFixturesClass');
        $method = $class->getMethod('barOperation');

        $this->description = $description;

        $this->beConstructedWith($stream, $method, $description);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\Writer\ParamsTable');
    }

    function it_writes_additional_properties(
        Operation $operation,
        Parameter $param1,
        Parameter $param2,
        Parameter $param3
    ) {
        $param1->getName()->willReturn('Foo');
        $param1->getType()->willReturn('string');
        $param1->getDescription()->willReturn('This is a desc of Foo');

        $param2->getName()->willReturn('Bar');
        $param2->getType()->willReturn('string');
        $param2->getDescription()->willReturn('This is a desc of Bar');

        $param3->getName()->willReturn('Baz');
        $param3->getType()->willReturn('string');
        $param3->getDescription()->willReturn('This is a desc of Baz');

        $operation->getParams()->willReturn([$param1, $param2, $param3]);
        $this->description->getOperation('BarOperation')->willReturn($operation);

        $string = <<<EOT
Additional Parameters
~~~~~~~~~~~~~~~~~~~~~


EOT;

        $this->stream->write($string)->shouldBeCalled();

        $this->write();
    }
}

class ParamsFixturesClass
{
    /**
     * @param $name      {BarOperation::Name}
     * @param $container {BarOperation::Container}
     * @param $options   {BarOperation}
     */
    public function barOperation($name, $container, array $options = [])
    {}
}