<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Framework\Http\Router\Exception;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Description of RequestNotMatchedException
 *
 * @author alexringo
 */
class RequestNotMatchedException extends \LogicException {
    
    private $request;
    
    public function __construct(ServerRequestInterface $request) {
        parent::__construct('Matches mot found.');
        $this->request = $request;
    }
    
    public function getRequest(): ServerRequestInterface {
        return $this->request;
    }
}
