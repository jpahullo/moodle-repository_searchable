<?php

use repository_searchable\usecase\files\SelectFilesUseCase;
use repository_searchable\usecase\files\SelectFilesCommand;

require_once($CFG->dirroot . '/repository/filesystem/lib.php');

class repository_searchable extends repository_filesystem {

    protected static $nitems_values = array(10, 20, 30, 50, 80, 130);
    protected $nitems_options;
    protected $keyword;
    protected $nitems;
    protected $keywordId;
    protected $lastKeywordId;
    protected $nitemsId;


    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array())
    {
        parent::__construct($repositoryid, $context, $options);
        $this->readonly = true;
        $this->nitems_options = array();
        foreach (self::$nitems_values as $option) {
            $this->nitems_options[] = (object)array(
                'label' => $option,
                'value' => $option,
            );
        }
    }

    public function check_login() {
        global $SESSION;
        $this->keyword = optional_param('searchable_keyword', '', PARAM_RAW);
        $default_nitems = reset(self::$nitems_values);
        $this->nitems = optional_param('searchable_nitems', $default_nitems, PARAM_INT);

        if (empty($this->keyword)) {
            $this->keyword = optional_param('s', '', PARAM_RAW);
        }
        //TODO: parameter "p" comes with directory name being selected.
        // I propose to forget about being recursive.
        $sess_keyword = 'searchable_'.$this->id.'_keyword';
        $last_sess_keyword = 'last_'.$sess_keyword;
        $sess_nitems = 'searchable_'.$this->id.'_nitems';
        if (isset($SESSION->{$sess_keyword})) {
            $SESSION->{$last_sess_keyword} = $SESSION->{$sess_keyword};
            unset($SESSION->{$sess_keyword});
        }
        if (!empty($this->keyword)) {
            $SESSION->{$sess_keyword} = $this->keyword;
        }
        if (empty($this->nitems)) {
            if (isset($SESSION->{$sess_nitems})) {
                $this->keyword = $SESSION->{$sess_nitems};
            }
        } else {
            $SESSION->{$sess_nitems} = $this->nitems;
        }
        return !empty($this->keyword);
    }

    public function print_login()
    {
        global $SESSION;
        $sess_keyword = 'searchable_'.$this->id.'_keyword';
        $last_sess_keyword = 'last_'.$sess_keyword;
        $sess_nitems = 'searchable_'.$this->id.'_nitems';

        unset($SESSION->{$sess_keyword});
        $keywordtext = isset($SESSION->{$last_sess_keyword})?$SESSION->{$last_sess_keyword}:'';

        $keyword = new stdClass();
        $keyword->label = get_string('keyword', 'repository_searchable').': ';
        $keyword->id    = 'input_text_keyword';
        $keyword->type  = 'text';
        $keyword->name  = 'searchable_keyword';
        $keyword->value = $keywordtext;

        $nitems = new stdClass();
        $nitems->label = get_string('nitems', 'repository_searchable').': ';
        $nitems->id    = 'input_text_nitems';
        $nitems->type  = 'select';
        $nitems->name  = 'searchable_nitems';
        $nitems->options = $this->nitems_options;

        $last_nitems_value = isset($SESSION->{$sess_nitems})?$SESSION->{$sess_nitems}:reset(self::$nitems_values);
        $last_nitems = new stdClass();
        $last_nitems->id    = 'last_input_text_nitems';
        $last_nitems->type  = 'hidden';
        $last_nitems->name  = 'last_searchable_nitems';
        $last_nitems->value = $last_nitems_value;

        if ($this->options['ajax']) {
            $form = array();
            $form['login'] = array($keyword, $nitems, $last_nitems);
            $form['nologin'] = true;
            $form['logouttext'] = get_string('newsearch', 'repository_searchable');
            $form['norefresh'] = true;
            $form['dynload'] = true;
            $form['nosearch'] = false;
            $form['issearchresult'] = true;
            $form['allowcaching'] = false; // indicates that login form cannot be cached in filepicker.js
            return $form;
        } else {
            $options = "";
            foreach (self::$nitems_values as $option) {
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
        $list = array();
        $list['list'] = array();
        $list['dynload'] = true;
        $list['nologin'] = true;
        $list['norefresh'] = true;
        $list['nosearch'] = true;
        $list['issearchresult'] = true;
        $list['allowcaching'] = false; // indicates that login form cannot be cached in filepicker.js
        $list['path'] = array(
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
                        $trail .= '/' . $part;
                        $list['path'][] = array('name' => $part, 'path' => $trail);
                    }
                }
            } else {
                $list['path'][] = array('name' => $path, 'path' => $path);
            }
        }

        // Retrieve list of files matching the given expression.
        $selection = new SelectFilesCommand($abspath, $this->keyword, $this->nitems);
        $filter = new SelectFilesUseCase();
        $fileslist = $filter->execute($selection);

        foreach ($fileslist as $file) {
            $node = array(
                'title' => $file,
                'source' => $path . '/' . $file,
                'size' => filesize($abspath . $file),
                'datecreated' => filectime($abspath . $file),
                'datemodified' => filemtime($abspath . $file),
                'thumbnail' => $OUTPUT->pix_url(file_extension_icon($file, 90))->out(false),
                'icon' => $OUTPUT->pix_url(file_extension_icon($file, 24))->out(false)
            );
            if (file_extension_in_typegroup($file, 'image') && ($imageinfo = @getimagesize($abspath . $file))) {
                // This means it is an image and we can return dimensions and try to generate thumbnail/icon.
                $token = $node['datemodified'] . $node['size']; // To prevent caching by browser.
                $node['realthumbnail'] = $this->get_thumbnail_url($path . '/' . $file, 'thumb', $token)->out(false);
                $node['realicon'] = $this->get_thumbnail_url($path . '/' . $file, 'icon', $token)->out(false);
                $node['image_width'] = $imageinfo[0];
                $node['image_height'] = $imageinfo[1];
            }
            $list['list'][] = $node;
        }
        $list['list'] = array_filter($list['list'], array($this, 'filter'));
        return $list;
    }

}
