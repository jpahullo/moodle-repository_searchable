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

use repository_searchable\usecase\UseCase;

/**
 *
 * @author Jordi Pujol-Ahulló <jpahullo@gmail.com>
 */
class BuildFileListUseCase implements UseCase
{
    /**
     * @var \core_renderer output html generator.
     */
    private $output;
    /**
     * @var \repository_searchable current repository.
     */
    private $repository;

    /**
     * Builds the list of filenames ready to be shown at the web.
     * @param \moodle_output $output
     * @param \repository_searchable $repository
     */
    public function __construct($output, $repository) {
        $this->output     = $output;
        $this->repository = $repository;
    }

    /**
     * For every single filename to be shown, this builds the data
     * structure necessary to be printed on the web.
     * @param BuildFileListCommand $command
     * @return array List of files to be printed.
     */
    public function execute($command) {
        $path    = $command->path();
        $abspath = $command->abspath();
        $result  = array();
        foreach ($command->files() as $file) {
            $result[] = $this->build_item($file, $path, $abspath);
        }
        $filteredresult = array_filter($result, array($this->repository, 'filter'));
        return $filteredresult;
    }

    /**
     * Builds the url of the given $icon.
     * @param string $icon to be shown.
     * @return string HTML with the formatted icon.
     */
    protected function pix_url($icon) {
        return $this->output->pix_url($icon)->out(false);
    }

    /**
     * From the URL for the thumbnail of an image file.
     * @param string $file path to the file (not the absolute path.
     * @param string $type type of thumbnail (thumb, icon).
     * @param type $token string to prevent browser caching.
     * @return string HTML formatted for the thumbnail.
     */
    protected function get_thumbnail_url($file, $type, $token) {
        return $this->repository->get_thumbnail_url($file, $type, $token)->out(false);
    }

    /**
     * Builds a file data item for being printed on the web. It also considers
     * if the file to list is an image and builds its thumbnail, if possible.
     * @param string $file filename.
     * @param string $path relative path inside the repository.
     * @param string $abspath absolute path.
     * @return array array with data to make appear a file in the web list.
     */
    protected function build_item($file, $path, $abspath) {
        $node      = array(
            'title'        => $file,
            'source'       => $path . '/' . $file,
            'size'         => filesize($abspath . $file),
            'datecreated'  => filectime($abspath . $file),
            'datemodified' => filemtime($abspath . $file),
            'thumbnail'    => $this->pix_url(file_extension_icon($file, 90)),
            'icon'         => $this->pix_url(file_extension_icon($file, 24)),
        );
        if (file_extension_in_typegroup($file, 'image') && ($imageinfo = @getimagesize($abspath . $file))) {
            // This means it is an image and we can return dimensions and try to generate thumbnail/icon.
            $token                 = $node['datemodified'] . $node['size']; // To prevent caching by browser.
            $node['realthumbnail'] = $this->get_thumbnail_url($path . '/' . $file, 'thumb', $token);
            $node['realicon']      = $this->get_thumbnail_url($path . '/' . $file, 'icon', $token);
            $node['image_width']   = $imageinfo[0];
            $node['image_height']  = $imageinfo[1];
        }
        return $node;
    }

}
