<?php


namespace tool\libs;

#[\Attribute]
class Support
{
    public string $name;
    public array $types;

    public function __construct(string $name, array $types)
    {
        $this->name = $name;
        $this->types = $types;
    }
}