<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:28
 * @lastModified 2023/11/9 上午10:50
 *
 */

namespace zap\cms;

use ArrayIterator;
use FilesystemIterator;
use InvalidArgumentException;
use LimitIterator;
use RegexIterator;
use SplFileInfo;

class SortingFilesystemIterator extends ArrayIterator
{
    public function __construct(string $path, int $flags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS)
    {
        parent::__construct(iterator_to_array(new FilesystemIterator($path, $flags)));
    }

    public function __call(string $name, array $arguments)
    {
        if (preg_match('/^sortBy(.*)/', $name, $m))
            return $this->sort('get' . $m[1]);
        return $this;
    }

    public function sort($method): SortingFilesystemIterator
    {
        if (!method_exists('SplFileInfo', $method)) throw new InvalidArgumentException(sprintf('Method "%s" does not exist in SplFileInfo', $method));

        $this->uasort(function(SplFileInfo $a, SplFileInfo $b) use ($method) { return (is_string($a->$method()) ? strnatcmp($a->$method(), $b->$method()) : $b->$method() - $a->$method()); });

        return $this;
    }

    public function limit(int $offset = 0, int $limit = -1)
    {
        return parent::__construct(iterator_to_array(new LimitIterator($this, $offset, $limit))) ?? $this;
    }

    public function match(string $regex, int $mode = RegexIterator::MATCH, int $flags = 0, int $preg_flags = 0)
    {
        return parent::__construct(iterator_to_array(new RegexIterator($this, $regex, $mode, $flags, $preg_flags))) ?? $this;
    }
}