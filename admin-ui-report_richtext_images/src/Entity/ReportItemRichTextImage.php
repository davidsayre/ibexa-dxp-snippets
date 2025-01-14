<?php


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ReportItemRichTextImage
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @var string
     */
    private $contentType;

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
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    private $fieldId;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @var string
     */
    private $fieldName;

    /**
     * @ORM\Column(type="string", length=20, nullable=false)
     * @var string
     */
    private $language;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    private $imageContentId;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @var string
     */
    private $imageAlias;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int
     */
    private $imageRawWidth;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int
     */
    private $imageRawHeight;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @var string
     */
    private $imageAlign;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $errors;

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

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): void
    {
        $this->contentType = $contentType;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
    }

    public function getFieldId(): int
    {
        return $this->fieldId;
    }

    public function setFieldId(int $fieldId): void
    {
        $this->fieldId = $fieldId;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function setFieldName(string $fieldName): void
    {
        $this->fieldName = $fieldName;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }


    public function getImageContentId(): int
    {
        return $this->imageContentId;
    }

    public function setImageContentId(int $imageContentId): void
    {
        $this->imageContentId = $imageContentId;
    }

    public function getImageAlias(): ?string
    {
        return $this->imageAlias;
    }

    public function setImageAlias(?string $imageAlias): void
    {
        $this->imageAlias = $imageAlias;
    }

    public function getImageRawWidth(): ?int
    {
        return $this->imageRawWidth;
    }

    public function setImageRawWidth(?int $imageRawWidth): void
    {
        $this->imageRawWidth = $imageRawWidth;
    }

    public function getImageRawHeight(): ?int
    {
        return $this->imageRawHeight;
    }

    public function setImageRawHeight(?int $imageRawHeight): void
    {
        $this->imageRawHeight = $imageRawHeight;
    }

    public function getImageAlign(): ?string
    {
        return $this->imageAlign;
    }

    public function setImageAlign(?string $imageAlign): void
    {
        $this->imageAlign = $imageAlign;
    }

    public function getErrors(): ?string
    {
        return $this->errors;
    }

    public function setErrors(?string $errors): void
    {
        $this->errors = $errors;
    }

}

?>