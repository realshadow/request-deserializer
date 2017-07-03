<?php

namespace Realshadow\RequestDeserializer\Testing\Fixtures;

use Illuminate\Support\Collection;
use JMS\Serializer\Annotation as JMS;

class CollectionTest
{

    /**
     * @var Collection $items
     *
     * @JMS\Type("Illuminate\Support\Collection<string>")
     */
    private $items;

    public function getItems()
    {
        return $this->items;
    }

}
