<?php

namespace App;

use Symfony\Component\Yaml\Yaml;

class Content
{
    private string $filename;

    /**
     * @var array<mixed>|null
     */
    private ? array $content = null;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return array<mixed>
     */
    public function getContent(): array
    {
        if (is_null($this->content)) {
            $this->content = Yaml::parseFile($this->filename);
        }

        return $this->content;
    }
}
