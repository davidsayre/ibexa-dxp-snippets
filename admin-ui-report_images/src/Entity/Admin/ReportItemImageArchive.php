<?php

namespace App\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ReportItemImageArchive
{

    const STATUS_NOT_IN_USE = 'not_in_use'; // no references
    const STATUS_IN_USE = 'in_use'; // used by any content
    const STATUS_IN_USE_ARCHIVE_ONLY = 'in_use_archive_only'; // used, but references are all in archive section

    const STATUS_MANUAL_SKIP = 'manual_skip'; // if admin decides to skip image entirely

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    private $contentId;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    private $version;

    /**
     * @ORM\Column(type="string", length=20, nullable=false)
     * @var string
     */
    private $language;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     * @var string
     */
    private $status;

    public function getId(): int
    {
        return $this->id;
    }

    public function getContentId(): int
    {
        return $this->contentId;
    }

    public function setContentId(int $contentId): void
    {
        $this->contentId = $contentId;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

}