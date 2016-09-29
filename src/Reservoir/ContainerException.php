<?php
namespace Reservoir;

use Exception;

/**
 * ContainerException
 */
class ContainerException extends Exception
{
    /**
     * @param string         $message  exception message
     * @param integer        $code     error code
     * @param Exception|null $previous Exception instance
     */
	public function __construct($message, $code = 0, Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
?>