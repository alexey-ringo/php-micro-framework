<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Action;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Description of AboutAction
 *
 * @author alexringo
 */
class AboutAction{
    public function __invoke(ServerRequestInterface $request)
    {
        return new HtmlResponse('I am a simple site');
    }
}
