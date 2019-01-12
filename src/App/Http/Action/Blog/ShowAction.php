<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Action\Blog;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Description of ShowAction
 *
 * @author alexringo
 */
class ShowAction {
    
    public function __invoke(ServerRequestInterface $request, callable $notFound) {
        $id = $request->getAttribute('id');
        if ($id > 2) {
            return $notFound($request);
        }
        return new JsonResponse(['id' => $id, 'title' => 'Post #' . $id]);
    }

}
