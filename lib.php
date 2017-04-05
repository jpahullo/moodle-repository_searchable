<?php

require_once($CFG->dirroot . '/repository/filesystem/lib.php');

class repository_searchable extends repository_filesystem {

    const PAGE_SIZE = 2;

    public function check_login() {
        global $SESSION;
        $this->keyword = optional_param('searchable_keyword', '', PARAM_RAW);
        if (empty($this->keyword)) {
            $this->keyword = optional_param('s', '', PARAM_RAW);
        }
        if (!empty($this->keyword)) {
            $this->keyword = "*" . $this->keyword . "*";
        }
        $sess_keyword = 'searchable_'.$this->id.'_keyword';
        if (empty($this->keyword) && optional_param('page', '', PARAM_RAW)) {
            // This is the request of another page for the last search, retrieve the cached keyword.
            if (isset($SESSION->{$sess_keyword})) {
                $this->keyword = $SESSION->{$sess_keyword};
            }
        } else if (!empty($this->keyword)) {
            // Save the search keyword in the session so we can retrieve it later.
            $SESSION->{$sess_keyword} = $this->keyword;
        }
        return !empty($this->keyword);
    }

    public function print_login()
    {
        $keyword = new stdClass();
        $keyword->label = get_string('keyword', 'repository_searchable').': ';
        $keyword->id    = 'input_text_keyword';
        $keyword->type  = 'text';
        $keyword->name  = 'searchable_keyword';
        $keyword->value = '';
        if ($this->options['ajax']) {
            $form = array();
            $form['login'] = array($keyword);
            $form['nologin'] = true;
            $form['norefresh'] = true;
            $form['nosearch'] = true;
            return $form;
        } else {
            echo <<<EOD
<table>
<tr>
<td>{$keyword->label}</td><td><input name="{$keyword->name}" type="text" /></td>
</tr>
</table>
<input type="submit" />
EOD;
        }
    }
    public function print_search()
    {
        return $this->print_login();
    }
    
    public function logout() {
        return $this->print_login();
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
        $list['manage'] = false;
        $list['dynload'] = true;
        $list['nologin'] = true;
        $list['norefresh'] = true;
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

        // Retrieve list of files and directories and sort them.
        $fileslist = array();
        $dirslist = array();
        $searcher = repository_searchable_filelist_generator($abspath, $this->keyword);
        $i = 0;
        foreach ($searcher as list($isfile, $file)) {
            if ($isfile) {
                $fileslist[] = $file;
                continue;
            }
            $dirslist[] = $file;
            $i++;
            if ($i >= self::PAGE_SIZE) {
                break;
            }
        }

        core_collator::asort($fileslist, core_collator::SORT_NATURAL);
        core_collator::asort($dirslist, core_collator::SORT_NATURAL);

        // Fill the $list['list'].
        foreach ($dirslist as $file) {
            $list['list'][] = array(
                'title' => $file,
                'children' => array(),
                'datecreated' => filectime($abspath . $file),
                'datemodified' => filemtime($abspath . $file),
                'thumbnail' => $OUTPUT->pix_url(file_folder_icon(90))->out(false),
                'path' => $path . '/' . $file
            );
        }
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

    public function search($search_text, $page = 0)
    {
        return $this->get_listing();
    }

}

function repository_searchable_filelist_generator($abspath, $filter)
{
    // Retrieve list of files and directories and sort them.
    if (!($dh = opendir($abspath))) {
//        var_dump("entro a no puc obrir directori");
        return;
    }
    while (($file = readdir($dh)) != false) {
        if ($file == '.' || $file == '..') {
//            var_dump("es .");
            continue;
        }
//        var_dump("filter => $filter, file => $file, match => " . (int)fnmatch($filter, $file, FNM_PERIOD));
        if (!fnmatch($filter, $file, FNM_PERIOD)) {
            continue;
        }
        if (is_file($abspath . $file)) {
            yield array(true, $file);
        } else {
            yield array(false, $file);
        }
    }
}

