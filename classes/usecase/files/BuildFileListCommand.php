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

use repository_searchable\usecase\Command;

/**
 * Command with the data to build the list of files to show.
 *
 * @author Jordi Pujol-Ahulló <jpahullo@gmail.com>
 */
class BuildFileListCommand implements Command
{

    private $files;
    private $path;
    private $abspath;

    public function __construct($files, $path, $abspath) {
        $this->files = $files;
        $this->path = $path;
        $this->abspath = $abspath;
    }

    public function files() {
        return $this->files;
    }

    public function path() {
        return $this->path;
    }
    public function abspath() {
        return $this->abspath;
    }
}
