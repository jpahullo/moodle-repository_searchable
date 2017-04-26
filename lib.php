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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/repository/filesystem/lib.php');

class repository_searchable extends repository_filesystem
{

    protected static $nitemsvalues = array(10, 20, 30, 50, 80, 130);
    protected $nitemsoptions;
    protected $keyword;
    protected $nitems;
    protected $keywordid;
    protected $lastkeywordid;
    protected $nitemsid;

    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
        parent::__construct($repositoryid, $context, $options);
        $this->readonly      = true;
        $this->nitemsoptions = array();
        foreach (self::$nitemsvalues as $option) {
            $this->nitemsoptions[] = (object) array(
                        'label' => $option,
                        'value' => $option,
            );
        }
    }

    public function check_login() {
        global $SESSION;
        $this->keyword = optional_param('searchable_keyword', '', PARAM_RAW);
        $defaultnitems = reset(self::$nitemsvalues);
        $this->nitems  = optional_param('searchable_nitems', $defaultnitems, PARAM_INT);

        if (empty($this->keyword)) {
            $this->keyword = optional_param('s', '', PARAM_RAW);
        }
        // TODO: parameter "p" comes with directory name being selected.
        // I propose to forget about being recursive.
        $sesskeyword     = 'searchable_' . $this->id . '_keyword';
        $lastsesskeyword = 'last_' . $sesskeyword;
        $sessnitems      = 'searchable_' . $this->id . '_nitems';
        if (isset($SESSION->{$sesskeyword})) {
            $SESSION->{$lastsesskeyword} = $SESSION->{$sesskeyword};
            unset($SESSION->{$sesskeyword});
        }
        if (!empty($this->keyword)) {
            $SESSION->{$sesskeyword} = $this->keyword;
        }
        if (empty($this->nitems)) {
            if (isset($SESSION->{$sessnitems})) {
                $this->keyword = $SESSION->{$sessnitems};
            }
        } else {
            $SESSION->{$sessnitems} = $this->nitems;
        }
        return !empty($this->keyword);
    }

    public function print_login() {
        global $SESSION;
        $sesskeyword     = 'searchable_' . $this->id . '_keyword';
        $lastsesskeyword = 'last_' . $sesskeyword;
        $sessnitems      = 'searchable_' . $this->id . '_nitems';

        unset($SESSION->{$sesskeyword});
        $keywordtext = isset($SESSION->{$lastsesskeyword}) ? $SESSION->{$lastsesskeyword} : '';

        $keyword        = new stdClass();
        $keyword->label = get_string('keyword', 'repository_searchable') . ': ';
        $keyword->id    = 'input_text_keyword';
        $keyword->type  = 'text';
        $keyword->name  = 'searchable_keyword';
        $keyword->value = $keywordtext;

        $nitems          = new stdClass();
        $nitems->label   = get_string('nitems', 'repository_searchable') . ': ';
        $nitems->id      = 'input_text_nitems';
        $nitems->type    = 'select';
        $nitems->name    = 'searchable_nitems';
        $nitems->options = $this->nitemsoptions;

        $lastnitemsvalue   = isset($SESSION->{$sessnitems}) ? $SESSION->{$sessnitems} : reset(self::$nitemsvalues);
        $lastnitems        = new stdClass();
        $lastnitems->id    = 'last_input_text_nitems';
        $lastnitems->type  = 'hidden';
        $lastnitems->name  = 'last_searchable_nitems';
        $lastnitems->value = $lastnitemsvalue;

        if ($this->options['ajax']) {
            $form                   = array();
            $form['login']          = array($keyword, $nitems, $lastnitems);
            $form['nologin']        = true;
            $form['logouttext']     = get_string('newsearch', 'repository_searchable');
            $form['norefresh']      = true;
            $form['dynload']        = true;
            $form['nosearch']       = false;
            $form['issearchresult'] = true;
            // Indicates that login form cannot be cached in filepicker.js.
            $form['allowcaching']   = false;
            return $form;
        } else {
            $options = "";
            foreach (self::$nitemsvalues as $option) {
                $options .= "<option value=\"$option\">$option</option>";
            }
            echo <<<EOD
<table>
<tr>
<td>{$keyword->label}</td><td><input name="{$keyword->name}" type="text" /></td>
<td>{$nitems->label}</td><td>
    <select name="{$nitems->name}"/>
        $options
    </select>
</td>
</tr>
</table>
<input type="submit" />
EOD;
        }
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
        $selection = new SelectFilesCommand($abspath, $this->keyword, $this->nitems);
        $filter    = new SelectFilesUseCase();
        $fileslist = $filter->execute($selection);

        foreach ($fileslist as $file) {
            $node      = array(
                'title'        => $file,
                'source'       => $path . '/' . $file,
                'size'         => filesize($abspath . $file),
                'datecreated'  => filectime($abspath . $file),
                'datemodified' => filemtime($abspath . $file),
                'thumbnail'    => $OUTPUT->pix_url(file_extension_icon($file, 90))->out(false),
                'icon'         => $OUTPUT->pix_url(file_extension_icon($file, 24))->out(false)
            );
            if (file_extension_in_typegroup($file, 'image') && ($imageinfo = @getimagesize($abspath . $file))) {
                // This means it is an image and we can return dimensions and try to generate thumbnail/icon.
                $token                 = $node['datemodified'] . $node['size']; // To prevent caching by browser.
                $node['realthumbnail'] = $this->get_thumbnail_url($path . '/' . $file, 'thumb', $token)->out(false);
                $node['realicon']      = $this->get_thumbnail_url($path . '/' . $file, 'icon', $token)->out(false);
                $node['image_width']   = $imageinfo[0];
                $node['image_height']  = $imageinfo[1];
            }
            $list['list'][] = $node;
        }
        $list['list'] = array_filter($list['list'], array($this, 'filter'));
        return $list;
    }

}
