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

namespace repository_searchable\usecase;

defined('MOODLE_INTERNAL') || die();

/**
 * Use case to execute some specific task o set of tasks to accomplish
 * the purpose of a use case.
 *
 * The specific $command passed to the execute() method contains all
 * necessary data to process the use case.
 *
 * @author Jordi Pujol-Ahulló <jpahullo@gmail.com>
 */
interface UseCase
{

    /**
     * @param Command $command
     */
    public function execute($command);
}
