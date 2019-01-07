<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Tests\App\Http\Action;

use App\Http\Action\HelloAction;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;
/**
 * Description of HelloActionTest
 *
 * @author alexringo
 */
class HelloActionTest extends TestCase {
    
    public function testGuest()
    {
        $action = new HelloAction();
        $request = new ServerRequest();
        $response = $action($request);
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('Hello, Guest!', $response->getBody()->getContents());
    }
    
    public function testJohn()
    {
        $action = new HelloAction();
        $request = (new ServerRequest())
            ->withQueryParams(['name' => 'John']);
        $response = $action($request);
        self::assertEquals('Hello, John!', $response->getBody()->getContents());
    }
    
}
