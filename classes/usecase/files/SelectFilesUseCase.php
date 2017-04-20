<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace repository_searchable\usecase\files;

/**
 *
 * @author Jordi Pujol-Ahulló <jpahullo@gmail.com>
 */
class SelectFilesUseCase implements \repository_searchable\usecase\UseCase
{

    /**
     *
     * @param SelectFilesCommand $usecase
     * @return type
     */
    public function execute($usecase)
    {
        $searcher     = $this->generator($usecase);
        $firstResults = array();
        $nitem        = 0;
        foreach ($searcher as $filename) {
            $firstResults[] = $filename;
            $nitem++;
            if ($nitem >= $usecase->nitems()) {
                break;
            }
        }
        \core_collator::asort($firstResults, \core_collator::SORT_NATURAL);
        return $firstResults;
    }

    /**
     *
     * @param SelectFilesCommand $usecase
     * @return generator
     */
    protected function generator($usecase)
    {
        // TODO: sort results.
        if (!($dh = opendir($usecase->abspath()))) {
            return;
        }

        $realFilter = "*" . $usecase->filter() . "*";
        while (($file       = readdir($dh)) != false) {
            if (!is_file($usecase->abspath() . $file)) {
                continue;
            }
            if (!fnmatch($realFilter, $file, FNM_PERIOD)) {
                continue;
            }
            yield $file;
        }
    }

}
