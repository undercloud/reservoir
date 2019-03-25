<?php
class Bat
{
	private $bar;

    public function __construct(Bar $bar)
    {
        $this->bar = $bar;
    }

    public function getBar()
    {
        return $this->bar;
    }
}