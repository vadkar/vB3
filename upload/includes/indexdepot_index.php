<?php
/**
 * http://www.indexdepot.com
 *
 * @author Vadims Karpuschkins
 */
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions_template.php');
require_once(DIR . '/includes/indexdepot_globals.php');

function indexdepot_new_post($vbulletin, $post)
{
    try {
        $db = $vbulletin->db;
        $sql = "SELECT p.postid AS p_id, p.title AS p_title, p.parentid AS p_parentid, p.dateline AS p_dateline,\n"
                . " p.username AS p_username, p.userid AS p_userid, p.pagetext AS p_pagetext, p.iconid AS p_iconid,\n"
                . " t.threadid AS t_id, t.title AS t_title, t.firstpostid AS t_firstpostid,\n"
                . " t.lastpostid AS t_lastpostid, t.lastpost AS t_lastpost, t.postusername AS t_postusername,\n"
                . " t.postuserid AS t_postuserid, t.lastposter AS t_lastposter, t.replycount AS t_replycount,\n"
                . " t.views AS t_views, t.dateline AS t_dateline,\n"
                . " f.forumid AS f_id , f.title AS f_title, p.visible AS p_visible\n"
                . "FROM " . TABLE_PREFIX . "post p\n"
                . "INNER JOIN " . TABLE_PREFIX . "thread t ON p.threadid = t.threadid\n"
                . "INNER JOIN " . TABLE_PREFIX . "forum f ON t.forumid = f.forumid\n"
                . "WHERE p.postid = " . $post['postid'];

        unset($post);
        $post = $db->query_first($sql);
        
        $solr = indexdepot_solrsearch_connection($vbulletin);

        $document = indexdepot_create_document($post);
        $solr->addDocument($document, false, true, true, 1000);
    } catch (Exception $e) {
        die(var_dump($e));
    }
}

function indexdepot_delete_post($vbulletin, $postinfo)
{
    global $vbulletin;
    
    $solr = indexdepot_solrsearch_connection($vbulletin);
    
    $solr->deleteById($postinfo['postid']);
}

function indexdepot_delete_post_diff($vbulletin, $postid)
{
    global $vbulletin;
    
    $solr = indexdepot_solrsearch_connection($vbulletin);
    
    $solr->deleteById($postid);
}

function indexdepot_delete_thread($vbulletin, $threadid)
{
    global $vbulletin;
    
    $solr = indexdepot_solrsearch_connection($vbulletin);
    
    $solr->deleteByQuery('t_id:'.$threadid);
}