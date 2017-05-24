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
class SessionAttribute
{
    const SESSION_PREFIX = 'repository_searchable';
    /**
     * List of session's attribute.
     * @var array
     */
    private static $instances = array();
    /**
     * Session's attribute name.
     * @var string
     */
    public $attribute;


    /**
     * Private constructor to use only from static builder.
     * @param string $attributename session's attribute name.
     */
    private function __construct($attributename) {
        global $SESSION;
        if (!isset($SESSION->{self::SESSION_PREFIX})) {
            $SESSION->{self::SESSION_PREFIX} = new \stdClass();
        }
        $this->attribute = $attributename;
    }

    /**
     * Generator for specific session's attributes.
     * @param string $attributename session's attribute name.
     * @param object $session current session.
     * @return SessionAttribute
     */
    public static function from($attributename) {
        if (!isset(self::$instances[$attributename])) {
            self::$instances[$attributename] = new static($attributename);
        }
        return self::$instances[$attributename];
    }

    /**
     * Sets the $newvalue to the session's attribute value.
     * If it is null, then it unsets the attribute.
     * @param mixed $newvalue new attribute value.
     */
    public function set($newvalue = null) {
        global $SESSION;
        if (null === $newvalue) {
            unset($SESSION->{self::SESSION_PREFIX}->{$this->attribute});
        }

        $SESSION->{self::SESSION_PREFIX}->{$this->attribute} = $newvalue;
    }

    /**
     * Gets the current value of the session's attribute or empty string if it
     * does not exist.
     * @return mixed current value of the session's attribute or empty string
     * if not set.
     */
    public function get() {
        global $SESSION;

        $sessionprefix = $SESSION->{self::SESSION_PREFIX};
        return (isset($sessionprefix->{$this->attribute})) ? $sessionprefix->{$this->attribute} : '';
    }

}
