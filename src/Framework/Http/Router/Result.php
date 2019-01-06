<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Framework\Http\Router;

/**
 * Description of Result
 *
 * @author alexringo
 */
class Result {
    
    private $name;
    private $handler;
    private $attributes;
    
    public function __construct($name, $handler, $attributes) {
        $this->name = $name;
        $this->handler = $handler;
        $this->attributes = $attributes;
    }
    
    public function getName(): string {
        return $this->name;
    }
    
    /**
     * @return mixed
     */
    public function getHandler() {
        return $this->handler;
    }
    
    public function getAttributes(): array {
        return $this->attributes;
    }
}
