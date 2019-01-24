<?php

namespace Framework\Container;

interface ContainerInterface
{
    /**
     * @param $id
     * @return mixed
     * @throws NotFoundExceptionInterface
     */
     public function get($id);
     public function has($id): bool;
}