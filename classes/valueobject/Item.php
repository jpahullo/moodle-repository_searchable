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
class Item
{
    public $key;
    public $sessionattribute;
    public $label;
    public $id;
    public $type;
    public $name;
    public $value;
    public $options;
    public $defaultvalue;

    private function __construct($name, $type, $value = '') {
        $this->key               = $name;
        $this->label             = get_string($name, 'repository_searchable') . ': ';
        $this->id                = 'input_' . $type . '_' . $name;
        $this->type              = $type;
        $this->name              = 'searchable_' . $name;
        $this->defaultvalue     = $value;
        $this->sessionattribute = \repository_searchable\valueobject\SessionAttribute::from($this->name);
        $formvalue              = $this->get_default_value($this->sessionattribute, $value);
        switch ($type) {
            case 'select':
                $this->options = $formvalue;
                break;
            default:
                $this->value   = $formvalue;
        }
    }

    public function is_valid($currentvalue) {
        return !empty($currentvalue);
    }

    public function set_default($currentvalue) {
        $this->sessionattribute->set($currentvalue);
    }

    public function get_default() {
        return $this->sessionattribute->get();
    }

    public static function from($name, $type, $value = '') {
        return new static($name, $type, $value);
    }

    protected function get_default_value($sessionattribute, $value) {
        if (!empty($value)) {
            if (!is_array($value)) {
                return $value;
            }
            $options = array_map(function($item) {
                return (object) array(
                            'label' => $item,
                            'value' => $item,
                );
            }, $value);
            return $options;
        }
        return $sessionattribute->get();
    }

}
