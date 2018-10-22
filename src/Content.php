<?php

namespace App;

use Symfony\Component\Yaml\Yaml;

class Content
{

    private $filename;

    private $content;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * [getContent description]
     * @return [type] [description]
     */
    public function getContent()
    {
        if (is_null($this->content)) {
            $this->content = Yaml::parseFile($this->filename);
        }

        return $this->content;
    }
}
