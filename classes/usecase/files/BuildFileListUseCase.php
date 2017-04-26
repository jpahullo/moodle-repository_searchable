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

use repository_searchable\usecase\UseCase;

/**
 *
 * @author Jordi Pujol-Ahulló <jpahullo@gmail.com>
 */
class BuildFileListUseCase implements UseCase
{

    private $output;
    private $repository;

    public function __construct($output, $repository) {
        $this->output = $output;
        $this->repository = $repository;
    }

    public function execute($command) {
        $path = $command->path();
        $abspath = $command->abspath();
        $result = array();
        foreach ($command->files() as $file) {
            $result[] = $this->buildItem($file, $path, $abspath);
        }
        return $result;
    }

    protected function pixUrl($icon) {
        return $this->output->pix_url($icon)->out(false);
    }

    protected function getThumbnailUrl($file, $type, $token) {
        return $this->repository->get_thumbnail_url($file, $type, $token)->out(false);
    }


    protected function buildItem($file, $path, $abspath) {
        $node      = array(
            'title'        => $file,
            'source'       => $path . '/' . $file,
            'size'         => filesize($abspath . $file),
            'datecreated'  => filectime($abspath . $file),
            'datemodified' => filemtime($abspath . $file),
            'thumbnail'    => $this->pixUrl(file_extension_icon($file, 90)),
            'icon'         => $this->pixUrl(file_extension_icon($file, 24)),
        );
        if (file_extension_in_typegroup($file, 'image') && ($imageinfo = @getimagesize($abspath . $file))) {
            // This means it is an image and we can return dimensions and try to generate thumbnail/icon.
            $token                 = $node['datemodified'] . $node['size']; // To prevent caching by browser.
            $node['realthumbnail'] = $this->getThumbnailUrl($path . '/' . $file, 'thumb', $token);
            $node['realicon']      = $this->getThumbnailUrl($path . '/' . $file, 'icon', $token);
            $node['image_width']   = $imageinfo[0];
            $node['image_height']  = $imageinfo[1];
        }
        return $node;
    }
}
