<?php

namespace PetrKnap\Symfony\MarkdownWeb\Model;

class Index
{
    /**
     * @var Page[]
     */
    private $pages;

    /**
     * @param Page[] $pages
     */
    public function __construct(array $pages)
    {
        $this->pages = $pages;
    }

    /**
     * @param string $rootDirectory
     * @param array $files
     * @param callable $urlModifier
     * @return $this
     */
    public static function fromFiles($rootDirectory, array $files, callable $urlModifier)
    {
        $pages = [];
        foreach ($files as $file) {
            $page = Page::fromFile($rootDirectory, $file, $urlModifier);
            $pages[$page->getParameters()["url"]] = $page;
        }

        return new static($pages);
    }

    public function getPages(array $filters, $sortBy = null, $pageNumber = null, $paginationStep = null)
    {
        $pages = [];
        foreach ($this->pages as $page) {
            foreach ($filters as $key => $values) {
                if (null === $values) {
                    continue 1;
                }
                if (!is_array($values)) {
                    $values = [$values];
                }
                $unexpected = false;
                if ('!' === $key[0]) {
                    $unexpected = true;
                    $key = substr($key, 1);
                }
                foreach ($values as $value) {
                    if ($unexpected === in_array($value, (array)@$page->getParameters()[$key])) {
                        continue 3;
                    }
                }
            }
            $pages[$page->getParameters()["url"]] = $page;
        }

        if (null !== $sortBy) {
            $sortBy = explode(":", $sortBy);
            if (!isset($sortBy[1])) {
                $sortBy[1] = "asc";
            }
            uasort($pages, function (Page $a, Page $b) use ($sortBy) {
                $aValue = @$a->{$sortBy[0]};
                $bValue = @$b->{$sortBy[0]};
                if ($aValue == $bValue) {
                    $return = 0;
                } elseif ($aValue > $bValue) {
                    $return = 1;
                } else {
                    $return = -1;
                }

                return "desc" === $sortBy[1] ? -1 * $return : $return;
            });
        }

        if ($pageNumber) {
            $pages = array_slice(
                $pages,
                ($pageNumber - 1) * $paginationStep,
                $paginationStep
            );
        }

        return $pages;
    }

    public function getPage($url)
    {
        return @$this->pages[$url];
    }
}
