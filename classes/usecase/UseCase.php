<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace repository_searchable\usecase;

/**
 */
interface UseCase
{
    /**
     * @param Command $command
     */
    public function execute($command);
}
