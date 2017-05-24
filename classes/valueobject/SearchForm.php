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

namespace repository_searchable\valueobject;

defined('MOODLE_INTERNAL') || die();

/**
 *
 * @author Jordi Pujol-Ahulló <jpahullo@gmail.com>
 */
class SearchForm
{
    protected static $nitemsvalues = array(10, 20, 50, 100);
    /**
     * @var int This repository id.
     */
    private $repoid;
    /**
     * @var array associative array for the data form.
     */
    private $definition;
    private $items;

    private function __construct($repoid) {
        $this->repoid           = $repoid;
        $this->items            = array();
        $this->items['keyword'] = Item::from('keyword', 'text');
        $this->items['nitems']  = Item::from('nitems', 'select', self::$nitemsvalues);
    }

    public static function from($repoid) {
        return new self($repoid);
    }

    public function build_form() {
        if (!isset($this->definition)) {
            $this->definition                   = array();
            $this->definition['login']          = $this->items;
            $this->definition['nologin']        = true;
            $this->definition['logouttext']     = get_string('newsearch', 'repository_searchable');
            $this->definition['norefresh']      = true;
            $this->definition['dynload']        = true;
            $this->definition['nosearch']       = false;
            $this->definition['issearchresult'] = true;
            // Indicates that login form cannot be cached in filepicker.js.
            $this->definition['allowcaching']   = false;
        }
        return $this->definition;
    }

    public function is_valid() {
        $defaultnitems = reset(self::$nitemsvalues);
        $keyword        = optional_param($this->items['keyword']->name, '', PARAM_RAW);
        $keyword        = optional_param('s', $keyword, PARAM_RAW);
        $nitems         = optional_param($this->items['nitems']->name, $defaultnitems, PARAM_INT);

        if (!empty($keyword)) {
            $this->items['keyword']->set_default($keyword);
            $this->items['nitems']->set_default($nitems);
        }

        return $this->items['keyword']->is_valid($keyword) && $this->items['nitems']->is_valid($nitems);
    }

    public function get_data() {
        $result = new \stdClass();
        $result->keyword = $this->items['keyword']->get_default();
        $result->nitems = $this->items['nitems']->get_default();
        return $result;
    }

}
