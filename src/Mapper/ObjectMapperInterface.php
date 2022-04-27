<?php
namespace Terrazza\Component\Dto\Mapper;

interface ObjectMapperInterface {
    /**
     * @param object $source
     * @param array $mapping
     * @return array|object|null
     */
    public function map(object $source, array $mapping);
}