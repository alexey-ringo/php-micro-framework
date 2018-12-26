<?php
namespace Framework\Http;

class Request {
    
    public function getQueryParams(): array {
        return $_GET;
    }
    
    public function getParseBody() {
        //Если в body не пришли данные - то null
        return $_POST ?: null;
    }
}