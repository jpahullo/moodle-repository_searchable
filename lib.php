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

use repository_searchable\usecase\files\SelectFilesUseCase;
use repository_searchable\usecase\files\SelectFilesCommand;
use repository_searchable\usecase\files\BuildFileListCommand;
use repository_searchable\usecase\files\BuildFileListUseCase;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/repository/filesystem/lib.php');

class repository_searchable extends repository_filesystem
{

    /**
     * Builds this repository in a read-only mode.
     * @param int $repositoryid repository id.
     * @param int $context context id.
     * @param array $options options for the repository.
     */
    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
        parent::__construct($repositoryid, $context, $options);
        $this->readonly      = true;
    }

    /**
     * Checks whether we have to show the search form.
     *
     * @return bool true if it has to show the search form.
     * false if search is done and we need to show results.
     */
    public function check_login() {
        return \repository_searchable\valueobject\SearchForm::from($this->id)->is_valid();
    }

    /**
     * Shows the search form only for the AJAX enabled web.
     *
     * @return array form elements of the search form for the repository AJAX.
     */
    public function print_login() {
        return \repository_searchable\valueobject\SearchForm::from($this->id)->build_form();
    }

    /**
     * Get the list of files and directories in that repository.
     *
     * @param string $path to browse.
     * @param string $page page number.
     * @return array list of files and folders.
     */
    public function get_listing($path = '', $page = '') {
        global $OUTPUT;
        $list                   = array();
        $list['list']           = array();
        $list['dynload']        = true;
        $list['nologin']        = true;
        $list['norefresh']      = true;
        $list['nosearch']       = true;
        $list['issearchresult'] = true;
        // Indicates that login form cannot be cached in filepicker.js.
        $list['allowcaching']   = false;
        $list['path']           = array(
            array('name' => get_string('root', 'repository_filesystem'), 'path' => '')
        );

        $path = trim($path, '/');
        if (!$this->is_in_repository($path)) {
            // In case of doubt on the path, reset to default.
            $path = '';
        }
        $abspath = rtrim($this->get_rootpath() . $path, '/') . '/';

        // Construct the breadcrumb.
        $trail = '';
        if ($path !== '') {
            $parts = explode('/', $path);
            if (count($parts) > 1) {
                foreach ($parts as $part) {
                    if (!empty($part)) {
                        $trail          .= '/' . $part;
                        $list['path'][] = array('name' => $part, 'path' => $trail);
                    }
                }
            } else {
                $list['path'][] = array('name' => $path, 'path' => $path);
            }
        }

        // Retrieve list of files matching the given expression.
        $form = \repository_searchable\valueobject\SearchForm::from($this->id);
        $formdata = $form->get_data();
        $selection = new SelectFilesCommand($abspath, $formdata->keyword, $formdata->nitems);
        $filter    = new SelectFilesUseCase();
        $fileslist = $filter->execute($selection);

        // Retrieve list of files to show on the web.
        $builder = new BuildFileListCommand($fileslist, $path, $abspath);
        $nodegenerator = new BuildFileListUseCase($OUTPUT, $this);
        $list['list'] = $nodegenerator->execute($builder);

        return $list;
    }

}