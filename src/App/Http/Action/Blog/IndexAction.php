<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Action\Blog;

use Zend\Diactoros\Response\JsonResponse;

/**
 * Description of IndexAction
 *
 * @author alexringo
 */
class IndexAction {
    public function __invoke() {
        return new JsonResponse([
            ['id' => 2, 'title' => 'The Second Post'],
            ['id' => 1, 'title' => 'The First Post'],
        ]);
    }
}
