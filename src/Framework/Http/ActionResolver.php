<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Framework\Http;

/**
 * Description of ActionResolver
 *
 * @author alexringo
 */
class ActionResolver {
    public function resolve($handler): callable
    {
        //В зависимости от типа $handler либо создаем объект класса с обработчиком (если строка с именем клссса) либо сразу вызываем Closure
        return \is_string($handler) ? new $handler() : $handler;
    }
}
