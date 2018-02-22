<?php
namespace TAO\Schema\Index\Mode;

class Unique extends BlueprintNative
{
    function blueprintCreateCommand(): string
    {
        return 'unique';
    }

    function blueprintDeleteCommand(): string
    {
        return 'dropUnique';
    }
}