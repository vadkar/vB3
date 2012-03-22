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
require_once('./global.php');
require_once(DIR . '/includes/indexdepot_globals.php');

print_cp_header($vbphrase['indexdepot_apache_solr_index_data']);
if ($_REQUEST['do'] == 'index') {
    try {
        /*
         * All posts will be indexed
         */
        $sql = "SELECT p.postid AS p_id, p.title AS p_title, p.parentid AS p_parentid, p.dateline AS p_dateline,\n"
                . " p.username AS p_username, p.userid AS p_userid, p.pagetext AS p_pagetext, p.iconid AS p_iconid,\n"
                . " t.threadid AS t_id, t.title AS t_title, t.firstpostid AS t_firstpostid,\n"
                . " t.lastpostid AS t_lastpostid, t.lastpost AS t_lastpost, t.postusername AS t_postusername,\n"
                . " t.postuserid AS t_postuserid, t.lastposter AS t_lastposter, t.replycount AS t_replycount,\n"
                . " t.views AS t_views, t.dateline AS t_dateline,\n"
                . " f.forumid AS f_id , f.title AS f_title, p.visible AS p_visible\n"
                . "FROM " . TABLE_PREFIX . "post p\n"
                . "INNER JOIN " . TABLE_PREFIX . "thread t ON p.threadid = t.threadid\n"
                . "INNER JOIN " . TABLE_PREFIX . "forum f ON t.forumid = f.forumid ";

        $posts = $db->query_read($sql);

        $solr = indexdepot_solrsearch_connection($vbulletin);

        $documentsCollection = null;
        $i = 0;
        $document = null;
        
        $number = $vbulletin->options['indexdepot_document_number'];
        
        while ($post = $db->fetch_array($posts)) {
            if ($post['p_visible'] == 1) {
                $document = indexdepot_create_document($post);
                $documentsCollection[] = $document;
                $document = null;
                if (($i % $number) == 0) {
                    $solr->addDocuments($documentsCollection);
                    $documentsCollection = null;
                }
                $i++;
            }
        }
        if ($documentsCollection != null) {
            $solr->addDocuments($documentsCollection);
            $documentsCollection = null;
        }

        $solr->commit();
        print_cp_message($vbphrase['indexdepot_apache_solr_index_data_ready']);
    } catch (Exception $e) {
        var_dump($e);
    }
}