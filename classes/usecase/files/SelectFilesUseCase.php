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

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the set of filenames to show on the repository.
 *
 * @author Jordi Pujol-Ahulló <jpahullo@gmail.com>
 */
class SelectFilesUseCase implements \repository_searchable\usecase\UseCase
{

    /**
     * Provides the list of files matching the given pattern.
     * @param SelectFilesCommand $usecase
     * @return type
     */
    public function execute($usecase) {
        $searcher     = $this->generator($usecase);
        $firstresults = array();
        $nitem        = 0;
        foreach ($searcher as $filename) {
            $firstresults[] = $filename;
            $nitem++;
            if ($nitem >= $usecase->nitems()) {
                break;
            }
        }
        \core_collator::asort($firstresults, \core_collator::SORT_NATURAL);
        return $firstresults;
    }

    /**
     * Provides a file list generator of filenames matching the
     * the given pattern.
     *
     * @param SelectFilesCommand $usecase
     * @return generator
     */
    protected function generator($usecase) {
        // TODO: sort results.
        if (!($dh = opendir($usecase->abspath()))) {
            return;
        }

        $realfilter = "*" . $usecase->filter() . "*";

        while (($file = readdir($dh)) != false) {
            if (!is_file($usecase->abspath() . $file)) {
                continue;
            }
            if (!fnmatch($realfilter, $file, FNM_PERIOD | FNM_CASEFOLD)) {
                continue;
            }
            yield $file;
        }
    }

}
