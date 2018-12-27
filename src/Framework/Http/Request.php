<?php
namespace Framework\Http;

class Request {
    
    private $queryParams;
    private $parsedBody;
    
    public function __construct(array $queryParams = [], $parsedBody = null) {
        $this->queryParams = $queryParams;
        $this->parsedBody = $parsedBody;
    }
    
    public function getQueryParams(): array {
        return $this->queryParams;
    }
    
    public function withQueryParams(array $query): self {
            //Для иммутабельности объекта
            //Любой новый вызов withQueryParams() не меняет сущ. объект, а создает его клон
        //$new = clone $this;
            //private переменные равнодоступны внутри класса для всех объектов этого класса
        //$new->$queryParams = $query;
        //return $new;
            
        $this->queryParams = $query;
        return $this;
    }
    
    public function getParsedBody() {
        return $this->parsedBody;
    }
    
    public function withParsedBody($data): self {
        //$new = clone $this;
        //$new->parsedBody = $data;
        //return $new;
        
        $this->parsedBody = $data;
        return $this;
    }
}