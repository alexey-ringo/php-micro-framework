<?php

namespace Tests\Framework\Http;

use Framework\Http\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase {
    
    //Прверка того, что с пустыми $_GET и $_POST,
    //$request->getQueryParams() - возвращает пустой массив,
    //а $request->getParsedBody() - возвращает null
    public function testEmpty(): void {
        
        $request = new Request();
        
        self::assertEquals([], $request->getQueryParams());
        self::assertNull($request->getParsedBody());
    }
    
    //Прверка того, что массив $data записывается в $_GET,
    //и возвращается с помощью $request->getQueryParams(),
    //а $request->getParsedBody() - возвращает null
    public function testQueryParams(): void {
       
        $request = (new Request())
            ->withQueryParams($data = [
                'name' => 'Alex',
                'age' => 47,
                ]);
        
        self::assertEquals($data, $request->getQueryParams());
        self::assertNull($request->getParsedBody());
    }
    
    //Прверка того, что массив $data записывается в $_POST,
    //и возвращается с помощью $request->getParsedBody(),
    //а $request->getQueryParams() - возвращает пустой массив
    public function testParsedBody(): void {
        
        $request = (new Request())
            ->withParsedBody($data = ['Title' => 'Title']);
        
        self::assertEquals([], $request->getQueryParams());
        self::assertEquals($data, $request->getParsedBody());
    }
    
    
}