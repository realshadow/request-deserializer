<?php

namespace App\Requests;

use Illuminate\Support\Collection;
use JMS\Serializer\Annotation as JMS;
use Realshadow\RequestDeserializer\Contracts\RequestInterface;


class CreateEventRequest implements RequestInterface
{

    /**
     * @var string $name
     *
     * @JMS\Since("1.x")
     * @JMS\Type("string")
     * @JMS\SerializedName("name")
     */
    private $name;

    /**
     * @var string|null $description
     *
     * @JMS\Since("1.x")
     * @JMS\Type("string")
     * @JMS\SerializedName("description")
     */
    private $description;

    /**
     * @var \DateTimeImmutable $startsAt
     *
     * @JMS\Since("1.x")
     * @JMS\Type("DateTimeImmutable")
     * @JMS\SerializedName("starts_at")
     */
    private $startsAt;

    /**
     * @var \DateTimeImmutable $endsAt
     *
     * @JMS\Since("1.x")
     * @JMS\Type("DateTimeImmutable")
     * @JMS\SerializedName("ends_at")
     */
    private $endsAt;

    /**
     * @var Collection $attendees
     *
     * @JMS\Since("1.x")
     * @JMS\Type("Illuminate\Support\Collection<App\Requests\AttendeeRequest>")
     * @JMS\SerializedName("attendees")
     */
    protected $attendees;


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
        return resource_path('schemas/create_event.json');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getStartsAt()
    {
        return $this->startsAt;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getEndsAt()
    {
        return $this->endsAt;
    }

    /**
     * @return Collection
     */
    public function getAttendees()
    {
        return $this->attendees;
    }

}
