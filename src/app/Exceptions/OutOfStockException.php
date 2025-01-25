<?php

namespace App\Exceptions;

use Exception;

class OutOfStockException extends Exception
{
    protected $data;

    public function __construct($message, $data = [])
    {
        parent::__construct($message);
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
