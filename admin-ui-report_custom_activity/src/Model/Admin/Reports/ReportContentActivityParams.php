<?php

namespace App\Model\Admin\Reports;

class ReportContentActivityParams {
    protected $dateField;
    protected $dateFrom;
    protected $dateTo;
    protected $sectionId;
    protected $excludeLocationIds;
    protected $contentType;
    protected $searchText;
    protected $title;
    protected $tagIds;
    protected $languageCode;
    protected $viewLanguage;
    protected $sortDir;

    /**
     * @return mixed
     */
    public function getDateField()
    {
        return $this->dateField;
    }

    /**
     * @param mixed $dateField
     */
    public function setDateField($dateField): void
    {
        $this->dateField = $dateField;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * @param \DateTime|null $dateFrom
     */
    public function setDateFrom($dateFrom): void
    {
        $this->dateFrom = $dateFrom;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    /**
     * @param \DateTime|null $dateTo
     */
    public function setDateTo($dateTo): void
    {
        $this->dateTo = $dateTo;
    }

    /**
     * @return mixed
     */
    public function getSectionId()
    {
        return $this->sectionId;
    }

    /**
     * @param mixed $sectionId
     */
    public function setSectionId($sectionId): void
    {
        $this->sectionId = $sectionId;
    }

    /**
     * @return mixed
     */
    public function getExcludeLocationIds()
    {
        return $this->excludeLocationIds;
    }

    /**
     * @param mixed $excludeLocationIds
     */
    public function setExcludeLocationIds($excludeLocationIds): void
    {
        $this->excludeLocationIds = $excludeLocationIds;
    }

    /**
     * @return mixed
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param mixed $contentType
     */
    public function setContentType($contentType): void
    {
        $this->contentType = $contentType;
    }

    /**
     * @return mixed
     */
    public function getSearchText()
    {
        return $this->searchText;
    }

    /**
     * @param mixed $searchText
     */
    public function setSearchText($searchText): void
    {
        $this->searchText = $searchText;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return array|null
     */
    public function getTagIds()
    {
        return $this->tagIds;
    }

    /**
     * @param array|null $tagIds
     */
    public function setTagIds($tagIds): void
    {
        $this->tagIds = $tagIds;
    }

    /**
     * @return mixed
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    /**
     * @param mixed $languageCode
     */
    public function setLanguageCode($languageCode): void
    {
        $this->languageCode = $languageCode;
    }

    /**
     * @return mixed
     */
    public function getViewLanguage()
    {
        return $this->viewLanguage;
    }

    /**
     * @param mixed $viewLanguage
     */
    public function setViewLanguage($viewLanguage): void
    {
        $this->viewLanguage = $viewLanguage;
    }

    /**
     * @return mixed
     */
    public function getSortDir()
    {
        return $this->sortDir;
    }

    /**
     * @param mixed $sortDir
     */
    public function setSortDir($sortDir): void
    {
        $this->sortDir = $sortDir;
    }

}

?>