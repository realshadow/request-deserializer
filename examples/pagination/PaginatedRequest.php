<?php

namespace App\Requests;

use JMS\Serializer\Annotation as JMS;
use Realshadow\RequestDeserializer\Contracts\RequestInterface;


class PaginatedRequest implements RequestInterface
{

    /**
     * @var int $page
     *
     * @JMS\Since("1.x")
     * @JMS\Type("integer")
     * @JMS\SerializedName("page")
     */
    private $page;

    /**
     * @var int $perPage
     *
     * @JMS\Since("1.x")
     * @JMS\Type("integer")
     * @JMS\SerializedName("per_page")
     */
    private $perPage;

    /**
     * @inheritdoc
     */
    public static function shouldValidate()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function getVersionConstraint()
    {
        return '1.0';
    }

    /**
     * @inheritdoc
     */
    public function getSchema()
    {
        return resource_path('schemas/pagination.json');
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return ($this->page - 1) * $this->perPage;
    }

}
