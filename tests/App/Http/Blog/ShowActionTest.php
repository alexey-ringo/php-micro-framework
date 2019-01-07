<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Tests\App\Http\Action\Blog;

use App\Http\Action\Blog\ShowAction;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

/**
 * Description of ShowActionTest
 *
 * @author alexringo
 */
class ShowActionTest extends TestCase {
    
    public function testSuccess()
    {
        $action = new ShowAction();
        $request = (new ServerRequest())
            ->withAttribute('id', $id = 2);
        $response = $action($request);
        self::assertEquals(200, $response->getStatusCode());
        self::assertJsonStringEqualsJsonString(
            json_encode(['id' => $id, 'title' => 'Post #' . $id]),
            $response->getBody()->getContents()
        );
    }
    
    public function testNotFound()
    {
        $action = new ShowAction();
        $request = (new ServerRequest())
            ->withAttribute('id', $id = 10);
        $response = $action($request);
        self::assertEquals(404, $response->getStatusCode());
        self::assertEquals('Undefined page', $response->getBody()->getContents());
    }
    
}
