<?php

namespace App\Block\Attribute\FormTypeMapper;

class TagsValueHolder {

    private $tags = array();

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

}

?>