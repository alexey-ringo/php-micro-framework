<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Framework\Http\Router\Exception;

/**
 * Description of RouteNotFoundException
 *
 * @author alexringo
 */
class RouteNotFoundException extends \LogicException {
    private $name;
    private $params;
    public function __construct($name, array $params, \Throwable $previous = null)
    {
        parent::__construct('Route "' . $name . '" not found.', 0, $previous);
        $this->name = $name;
        $this->params = $params;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getParams(): array
    {
        return $this->params;
    }
}
