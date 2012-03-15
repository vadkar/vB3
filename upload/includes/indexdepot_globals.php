<?php
/**
 * IndexDepot - vBulletin 3.x Solr Search
 * Copyright (c) 2012 IndexDepot
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * @copyright  IndexDepot 2012
 * @author Vadims Karpuschkins
 * @license LGPL
 */
require_once(DIR . '/includes/Indexdepot/Solr/Service.php');
require_once(DIR . '/includes/Apache/Solr/Document.php');

function indexdepot_solrsearch_connection($vbulletin)
{
    $scheme = ($vbulletin->options['indexdepot_scheme']) ? 'https://' : 'http://';
    $username = ($vbulletin->options['indexdepot_auth']) ? $vbulletin->options['indexdepot_username'] : '';
    $password = ($vbulletin->options['indexdepot_auth']) ? $vbulletin->options['indexdepot_password'] : '';
    $solr = new Indexdepot_Solr_Service($scheme,
                                        $vbulletin->options['indexdepot_host'],
                                        $vbulletin->options['indexdepot_port'],
                                        $vbulletin->options['indexdepot_path'],
                                        $username,
                                        $password);
    return $solr;
}

function indexdepot_create_document($post)
{    
    $document = new Apache_Solr_Document();
    $document->p_id = $post['p_id'];
    $document->p_title = $post['p_title'];
    $document->p_parentid = $post['p_parentid'];
    $document->p_username = $post['p_username'];
    $document->p_userid = $post['p_userid'];
    $document->p_pagetext = $post['p_pagetext'];
    $document->p_iconid = $post['p_iconid'];
    $document->p_dateline = $post['p_dateline'];

    $document->t_id = $post['t_id'];
    $document->t_title = $post['t_title'];
    $document->t_firstpostid = $post['t_firstpostid'];
    $document->t_lastpostid = $post['t_lastpostid'];
    $document->t_lastpost = $post['t_lastpost'];
    $document->t_postusername = $post['t_postusername'];
    $document->t_postuserid = $post['t_postuserid'];
    $document->t_lastposter = $post['t_lastposter'];
    $document->t_replycount = $post['t_replycount'];
    $document->t_views = $post['t_views'];
    $document->t_dateline = $post['t_dateline'];

    $document->f_id = $post['f_id'];
    $document->f_title = $post['f_title'];
    return $document;
}