<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Bukharov Sergey
 * Date: 15.09.11
 * Time: 12:32
 */

interface Evil_Parser_Interface
{
    /**
     * Return array of content. if what omitted return all.
     * Если категория задана, возвращать массив, состоящий из однйо категории,
     * и в нем уже массив с контентом
     * @abstract
     * @param null $what
     * @return array of
     *                   'category_1_name' => [
     *                                '0' or 'name' =>
     *                                          ['name' => name
     *                                           'desc' => desc]
     *                                '1' or 'name' =>
     *                                          ['name' => name
     *                                           'desc' => desc]
     *                                  ]
     *                  'category_2_name' => [
     *                                '0' or 'name' =>
     *                                          ['name' => name
     *                                           'desc' => desc]
     *                                '1' or 'name' =>
     *                                          ['name' => name
     *                                           'desc' => desc]
     *                                  ]
     *
     */
    function parse($what = null);
}
 
