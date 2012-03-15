<?php

/**
 * http://www.indexdepot.com
 *
 * @author Vadims Karpuschkins
 */
 
require_once(DIR . '/includes/Apache/Indexdepot.php');
require_once(DIR . '/includes/Apache/Solr/Document.php');

function indexdepot_solrsearch_connection($vbulletin)
{
    $scheme = ($vbulletin->options['indexdepot_scheme']) ? 'https://' : 'http://';
    $username = ($vbulletin->options['indexdepot_auth']) ? $vbulletin->options['indexdepot_username'] : '';
    $password = ($vbulletin->options['indexdepot_auth']) ? $vbulletin->options['indexdepot_password'] : '';
    $solr = new Indexdepot_Apache_Solr_Service($scheme,
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