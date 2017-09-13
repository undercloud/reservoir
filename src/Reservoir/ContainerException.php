<?php
namespace Reservoir;

use Exception;

/**
 * ContainerException
 *
 * @category IoC\DI
 * @package  Reservoir
 * @author   undercloud <lodashes@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     http://github.com/undercloud/reservoir
 */
class ContainerException extends Exception
{
    /**
     * Initialize instance
     *
     * @param string         $message  exception message
     * @param integer        $code     error code
     * @param Exception|null $previous Exception instance
     */
	public function __construct($message, $code = 0, Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
