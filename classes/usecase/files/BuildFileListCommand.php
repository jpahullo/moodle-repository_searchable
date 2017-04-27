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

use repository_searchable\usecase\Command;

/**
 * Command with the data to build the list of files to show.
 *
 * @author Jordi Pujol-Ahulló <jpahullo@gmail.com>
 */
class BuildFileListCommand implements Command
{
    /**
     * @var array List of filenames matching a given pattern.
     */
    private $files;
    /**
     * @var string Path inside the repository.
     */
    private $path;
    /**
     * @var string Absolute path to the repository directory.
     */
    private $abspath;

    /**
     * Builds the command with all necessary information.
     * @param array $files
     * @param string $path
     * @param string $abspath
     */
    public function __construct($files, $path, $abspath) {
        $this->files   = $files;
        $this->path    = $path;
        $this->abspath = $abspath;
    }

    /**
     * Array of filenames matching the pattern.
     * @return array
     */
    public function files() {
        return $this->files;
    }

    /**
     * Path inside the repository.
     * @return string
     */
    public function path() {
        return $this->path;
    }

    /**
     * Absolute path to the repository's directory.
     * @return string
     */
    public function abspath() {
        return $this->abspath;
    }

}
