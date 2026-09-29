<?php

namespace EventbriteKit\Entity;

use Doctrine\ORM\Mapping as ORM;
use ClassKit\Entity\Core\UpdatedGuidEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="EventbriteKit_events",
 * 		indexes={
 *          @ORM\Index(name="idx_start_date", columns={"startDate"}),
 *          @ORM\Index(name="idx_end_date", columns={"endDate"}),
 * 		}
 * )
 */
class Event extends UpdatedGuidEntity
{
    /**
     * @ORM\Column(type="string", length=64, nullable=false, unique=true)
     */
    protected string $EventbriteKitId;
    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected string $name;
    /**
     * @ORM\Column(type="string", length=512, nullable=false)
     */
    protected string $url;
    /**
     * @ORM\Column(type="string", length=256, nullable=true, options={"default": null})
     */
    protected ?string $venue;
    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     */
    protected \DateTimeImmutable $startDate;
    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     */
    protected \DateTimeImmutable $endDate;
    /**
     * @ORM\Column(type="text", nullable=true, options={"default": null})
     */
    protected ?string $description;
    /**
     * @ORM\Column(type="string", length=1024, nullable=true, options={"default": null})
     */
    protected ?string $image;

    /**
     * Find the event by `EventbriteKitId` (or create a new one) and overwrite every field from `$data`.
     *
     * The event is not persisted; the caller must persist and flush it.
     *
     * @param array{EventbriteKitId: string, name: string, url: string, venue: ?string, startDate: \DateTimeImmutable, endDate: \DateTimeImmutable, description: ?string, image: ?string} $data
     *
     * @return self
     */
    public static function createOrUpdate(array $data = [])
    {
        $event = self::getByColumnAndValue('EventbriteKitId', $data['EventbriteKitId']);
        if (!$event instanceof self) {
            $event = new self();
        }
        $event->setEventbriteKitId($data['EventbriteKitId']);
        $event->setName($data['name']);
        $event->setUrl($data['url']);
        $event->setVenue($data['venue']);
        $event->setStartDate($data['startDate']);
        $event->setEndDate($data['endDate']);
        $event->setDescription($data['description']);
        $event->setImage($data['image']);
        return $event;
    }

    /**
     * Get the value of name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param mixed $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of url
     *
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the value of url
     *
     * @param mixed $url
     *
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the value of venue
     *
     * @return mixed
     */
    public function getVenue()
    {
        return $this->venue;
    }

    /**
     * Set the value of venue
     *
     * @param mixed $venue
     *
     * @return self
     */
    public function setVenue($venue)
    {
        $this->venue = $venue;

        return $this;
    }

    /**
     * Get the value of startDate
     *
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Start date formatted with a date() format string.
     *
     * @return string|null
     */
    public function getStartDateFormatted(string $format = 'Y-m-d H:i:s')
    {
        return $this->getStartDate()?->format($format);
    }

    /**
     * Set the value of startDate
     *
     * @param mixed $startDate
     *
     * @return self
     */
    public function setStartDate(\DateTimeImmutable $startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get the value of endDate
     *
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * End date formatted with a date() format string.
     *
     * @return string|null
     */
    public function getEndDateFormatted(string $format = 'Y-m-d H:i:s')
    {
        return $this->getEndDate()?->format($format);
    }

    /**
     * Set the value of endDate
     *
     * @param mixed $endDate
     *
     * @return self
     */
    public function setEndDate(\DateTimeImmutable $endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get the value of description
     *
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @param mixed $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of image
     *
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set the value of image
     *
     * @param mixed $image
     *
     * @return self
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get the value of EventbriteKitId
     *
     * @return mixed
     */
    public function getEventbriteKitId()
    {
        return $this->EventbriteKitId;
    }

    /**
     * Set the value of EventbriteKitId
     *
     * @param mixed $EventbriteKitId
     *
     * @return self
     */
    public function setEventbriteKitId($EventbriteKitId)
    {
        $this->EventbriteKitId = $EventbriteKitId;

        return $this;
    }
}
