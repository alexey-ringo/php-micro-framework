<?php
namespace Framework\Http;

class RequestFactory { 
    public static function fromGlobals(array $query = null, array $body = null): Request {
        
        //либо заполняем переданными $query и $body
        //либо при отсутствии - берем из суперглоб
        return (new Request())
        ->withQueryParams($query ?: $_GET)
        ->withParsedBody($body ?: $_POST);
    }
}