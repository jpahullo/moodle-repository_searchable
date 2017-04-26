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
 * Use case to choose files given a pattern from the user.
 *
 * @author Jordi Pujol-Ahulló <jpahullo@gmail.com>
 */
class SelectFilesCommand implements \repository_searchable\usecase\Command
{

    /**
     * Directory where to find files matching a given pattern.
     * @var string
     */
    private $abspath;

    /**
     * The given pattern to match
     * @var string
     */
    private $filter;

    /**
     * Number of filenames as maximum to return.
     * @var int
     */
    private $nitems;

    /**
     * Builds the command.
     * @param string $abspath
     * @param string $filter
     * @param int $nitems
     */
    public function __construct($abspath, $filter, $nitems) {
        $this->abspath = $abspath;
        $this->filter  = $filter;
        $this->nitems  = $nitems;
    }

    /**
     * Directory to look for files matching a given pattern.
     * @return string
     */
    public function abspath() {
        return $this->abspath;
    }

    /**
     * The pattern provided by the user.
     * @return string
     */
    public function filter() {
        return $this->filter;
    }

    /**
     * Number of filenames to return as maximum.
     * @return type
     */
    public function nitems() {
        return $this->nitems;
    }

}
