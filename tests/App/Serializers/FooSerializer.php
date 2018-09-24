<?php

namespace Railken\Lem\Tests\App\Serializers;

use Illuminate\Support\Collection;
use Railken\Lem\Contracts\EntityContract;
use Railken\Lem\Serializer;

class FooSerializer extends Serializer
{
    /**
     * Serialize entity.
     *
     * @param \Railken\Lem\Contracts\EntityContract $entity
     * @param \Illuminate\Support\Collection        $select
     *
     * @return \Railken\Bag
     */
    public function serialize(EntityContract $entity, Collection $select = null)
    {
        $bag = parent::serialize($entity, $select);

        // ...

        return $bag;
    }
}
