<?php
namespace Reservoir\Tests;

use Reservoir\Di;
use PHPUnit_Framework_TestCase;

class ReservoirSetup extends PHPUnit_Framework_TestCase
{
	protected $di;

    public function setUp()
    {
        error_reporting(-1);
        date_default_timezone_set('UTC');
        $this->di = new Di;
    }
}
