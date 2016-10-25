<?php
class Baz
{
    private $foo;

    public function __construct(Foo $foo)
    {
        $this->foo = $foo;
    }

    public function getFoo()
    {
        return $this->foo;
    }

    public function quux(Quux $quux)
    {
        return $quux;
    }
    
    public static function staticQuux(Quux $quux)
    {
        return $quux;
    }
}
?>
