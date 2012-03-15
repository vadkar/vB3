<?php
/**
 * http://www.indexdepot.com
 *
 * @author Vadims Karpuschkins
 */
error_reporting(E_ALL & ~E_NOTICE & ~8192);

define('THIS_SCRIPT', 'search');
define('CSRF_PROTECTION', true);
define('ALTSEARCH', true);

// get special phrase groups
$phrasegroups = array('search', 'inlinemod', 'prefix');

// get special data templates from the datastore
$specialtemplates = array(
	'iconcache',
	'searchcloud'
);

// pre-cache templates used by all actions
$globaltemplates = array(
	'humanverify',
	'optgroup',
	'search_forums',
	'search_results',
	'search_results_postbit', // result from search posts
	'search_results_postbit_lastvisit',
	'threadbit', // result from search threads
	'threadbit_deleted', // result from deleted search threads
	'threadbit_lastvisit',
	'threadbit_announcement',
	'newreply_reviewbit_ignore',
	'threadadmin_imod_menu_thread',
	'threadadmin_imod_menu_post',
	'tag_cloud_link',
	'tag_cloud_box_search',
	'tag_cloud_headinclude'
);

require_once('./global.php');
require_once(DIR . '/includes/functions_search.php');
require_once(DIR . '/includes/functions_forumlist.php');
require_once(DIR . '/includes/functions_misc.php');
require_once(DIR . '/includes/indexdepot_globals.php');

if (!($permissions['forumpermissions'] & $vbulletin->bf_ugp_forumpermissions['cansearch']))
{
	print_no_permission();
}

if (!$vbulletin->options['enablesearches'])
{
	eval(standard_error(fetch_error('searchdisabled')));
}

// #############################################################################

$globals = array(
	'query'          => TYPE_STR,
	'searchuser'     => TYPE_STR,
	'exactname'      => TYPE_BOOL,
	'starteronly'    => TYPE_BOOL,
	'tag'            => TYPE_STR, // TYPE_STR, because that's what the error cond for intro expects
	'forumchoice'    => TYPE_ARRAY,
	'prefixchoice'   => TYPE_ARRAY_NOHTML,
	'childforums'    => TYPE_BOOL,
	'titleonly'      => TYPE_BOOL,
	'showposts'      => TYPE_BOOL,
	'searchdate'     => TYPE_NOHTML,
	'beforeafter'    => TYPE_NOHTML,
	'sortby'         => TYPE_NOHTML,
	'sortorder'      => TYPE_NOHTML,
	'replyless'      => TYPE_UINT,
	'replylimit'     => TYPE_UINT,
	'searchthreadid' => TYPE_UINT,
	'saveprefs'      => TYPE_BOOL,
	'quicksearch'    => TYPE_BOOL,
	'searchtype'     => TYPE_BOOL,
	'exclude'        => TYPE_NOHTML,
	'nocache'        => TYPE_BOOL,
	'ajax'           => TYPE_BOOL,
	'humanverify'    => TYPE_ARRAY,
	'userid'         => TYPE_UINT
);

$vbulletin->input->clean_array_gpc('r', array(
	'doprefs'    => TYPE_NOHTML,
	'searchtype' => TYPE_BOOL,
	'searchid'   => TYPE_UINT,
));
$itemdsids = array();
$itemcount = 0;

if ($_REQUEST['do'] == 'process')
{   
    $prefs = array();
    if ($_POST['do'] == 'process')
    {
        $vbulletin->input->clean_array_gpc('p', $globals);
        $search['showposts']  = $vbulletin->GPC['showposts'];        
        $prefs['titleonly']   = $vbulletin->GPC['titleonly'];
        $prefs['searchdate']  = $vbulletin->GPC['searchdate'];
        $prefs['beforeafter'] = $vbulletin->GPC['beforeafter'];
        $prefs['sortby']      = $vbulletin->GPC['sortby'];
        $prefs['sortorder']   = $vbulletin->GPC['sortorder'];
        $prefs['replyless']   = $vbulletin->GPC['replyless'];
        $prefs['replylimit']  = $vbulletin->GPC['replylimit'];
        $prefs['searchtype']  = $vbulletin->GPC['searchtype'];
        $prefs['lastvisited'] = $vbulletin->GPC['bblastvisit'];
        $prefs['exactname']   = $vbulletin->GPC['exactname'];
        $prefs['starteronly'] = $vbulletin->GPC['starteronly'];
        $prefs['forumchoice'] = $vbulletin->GPC['forumchoice'];
        $prefs['childforums'] = $vbulletin->GPC['childforums'];
        $prefs['userid']      = $vbulletin->GPC['bbuserid'];
        $prefs['quicksearch'] = $vbulletin->GPC['quicksearch'];
    }

    if (!$_GET['showposts']) {
    } else {
        $vbulletin->GPC['pagenumber']  = $_GET['pagenumber'];
        $search['showposts']  = $_GET['showposts'];
        $prefs['titleonly']   = $_GET['titleonly'];
        $prefs['searchdate']  = $_GET['searchdate']; 
        $prefs['beforeafter'] = $_GET['beforeafter'];
        $prefs['sortby']      = $_GET['sortby'];
        $prefs['sortorder']   = $_GET['sortorder']; 
        $prefs['replyless']   = $_GET['replyless']; 
        $prefs['replylimit']  = $_GET['replylimit']; 
        $prefs['searchtype']  = $_GET['searchtype']; 
        $prefs['lastvisited'] = $_GET['lastvisited'];
        $prefs['exactname']   = $_GET['exactname'];
        $prefs['starteronly'] = $_GET['starteronly'];
        $prefs['forumchoice'] = $_GET['forumchoice'];
        $prefs['childforums'] = $_GET['childforums'];
        $prefs['userid']      = $_GET['userid'];
    }
    /* init the variables */
    $search = $_REQUEST;
    require_once(DIR . '/includes/functions_forumdisplay.php');
    
    $vbulletin->input->clean_array_gpc('r',  array(
            'pagenumber' => TYPE_INT,
            'perpage'    => TYPE_INT,
            'page'    => TYPE_INT

    ));
    
    /* init solr attributes */
    $filters = array();    
    $query = '';

    /* get solr connection */
    try{
        $solr = indexdepot_solrsearch_connection($vbulletin);
    } catch (Exception $e) {
        var_dump($e);
    }
    
    
    
    /* differentiate between usersearch and textsearch */
    $query = isset($_REQUEST['query']) ? trim($_REQUEST['query']) : null;
    $searchuser = isset($_REQUEST['searchuser']) ? trim($_REQUEST['searchuser']) : null;
    
    $displayWords = '<b><u>' . $query . '</u></b>';  
    
    if (! empty($searchuser) && empty($query)) {        
        if ($prefs['exactname']) {
            $query = 'p_username:' . $searchuser;
        } else if (!$prefs['exactname']) {
            $query = 'p_username:' . $searchuser . '~';
        }
        if ($prefs['starteronly']) {
            $search['showposts'] = 0;
        } else if (!$prefs['starteronly']) {
            $search['showposts'] = 1;
        }
        $displayWords = '<b><u>' . $searchuser . '</u></b>';
    } else if (empty($query)) {
        $query = '*:*';      
    }
        
    $action = 'process';
    
    
    if (!$prefs['quicksearch']) {
        /* forum filter */
        $db = $vbulletin->db;
        $searchforums = array();
        if ($prefs['forumchoice'][0] == '0') {
            /* search every forum */
        } else {
            if ($prefs['forumchoice'][0] == 'subscribed') {
                $sql = "SELECT forumid "
                     . "FROM subscribeforum "
                     . "WHERE userid = " . $prefs['userid'];
                $ids = $db->query_read($sql);
                while ($id = $db->fetch_array($ids)) {
                    $searchforums[] = $id['forumid'];
                }
                unset($prefs['forumchoice'][0]);
            }
            $searchforums = array_merge($searchforums, $prefs['forumchoice']);

            /* get childforums only if the checkbox is activated */
            if ($prefs['childforums'] == 1) {
                foreach ($prefs['forumchoice'] as $key => $value) {
                    /* get a childforumlist form database */
                    $sql = "SELECT childlist\n"
                         . "FROM forum\n"
                         . "WHERE forumid = " . $value;
                    $result = $db->query_first($sql);
                    $childlist = explode(',', $result['childlist']);
                    array_pop($childlist);
                    $sql = '';

                    /* combine arrays */
                    $searchforums = array_merge($searchforums, $childlist);
                    unset($childlist);
                }
                /* remove duplicates */
                $searchforums = array_unique($searchforums);
            }
            /* build query */
            $filterquery = array();
            foreach ($searchforums as $key => $value) {
                $filterquery[] = 'f_id:' . $value;                
            }
            $filters['fq'][] = 'fq:(' .  implode($filterquery, ' OR ') . ')';
        }

        /* build solr filter */
        if ($prefs['titleonly'] == 1) {
            $filters['defType'] = 'edismax';
            $filters['qf'] = 'title';
        }
        /* count filter */
        if ($prefs['replyless'] == 0) {
            $filters['fq'][] = 't_replycount:[' . $prefs['replylimit'] . ' TO *]';
        } else if ($prefs['replyless'] == 1) {
            $filters['fq'][] = 't_replycount:[* TO ' . $prefs['replylimit'] . ']';
        }

        /* sorting filter */
        $sort = null;
        if ($prefs['sortorder'] == 'ascending') {
            $sort = ' asc';
        } elseif ($prefs['sortorder'] == 'descending') {
            $sort = ' desc';
        }
        switch ($prefs['sortby']) {
            case 'rank':
                $filters['sort'] = 'score' . $sort;
                break;
            case 'title':
                $filters['sort'] = 't_title' . $sort;
                break;
            case 'replycount':
                $filters['sort'] = 't_replycount' . $sort;
                break;
            case 'views':
                $filters['sort'] = 't_views' . $sort;
                break;
            case 'threadstart':
                $filters['sort'] = 't_dateline' . $sort;
                break;
            case 'lastpost':
                $filters['sort'] = 't_lastpost' . $sort;
                break;
            case 'postusername':
                $filters['sort'] = 't_postusername' . $sort;
                break;
            case 'forum':
                $filters['sort'] = 'f_id' . $sort;
                break;
        }
        /* time filter */
        $timethen = 0;
        switch ($prefs['searchdate']) {
            case 0:
                $timethen = 0;
                break;
            case 'lastvisited':
                $timethen = $prefs['lastvisited'];
                break;
            case 1:
                $timethen = strtotime('-1 day');
                break;
            case 7:
                $timethen = strtotime('-1 week');
                break;
            case 30:
                $timethen = strtotime('-1 month');
                break;
            case 90:
                $timethen = strtotime('-3 months');
                break;
            case 180:
                $timethen = strtotime('-6 months');
                break;
            case 365:
                $timethen = strtotime('-1 year');
                break;
        }
        if ($prefs['beforeafter'] == 'after') {
            $filters['fq'][] = 'p_dateline:[' . $timethen . ' TO ' . strtotime('now') . ']';
        } elseif ($prefs['beforeafter'] == 'before') {
            $filters['fq'][] = 'p_dateline:[0 TO ' . $timethen . ']';
        }
    }

    if ($vbulletin->GPC['pagenumber'] == 0) {
        $vbulletin->GPC['pagenumber'] = 1;
    }
    
    /* display options */
    $first = ($vbulletin->GPC['pagenumber'] == 1) ? 1 : $vbulletin->GPC['pagenumber'] * $vbulletin->options['searchperpage'] - $vbulletin->options['searchperpage']+1;    
    $show['results'] = 1;
    $show['forumlink'] = 1;
    $show['threadicons'] = 1;
    $show['threadicon'] = 1;
    $show['inlinemod'] = 0;
    $show['popups'] = 1;
    $threadcolspan = 7;
    $announcecolspan = 6;
    
    if ($show['inlinemod']) {
        $threadcolspan++;
        $announcecolspan++;
    }
    if (!$show['threadicons']) {
        $threadcolspan--;
        $announcecolspan--;
    }
    
    /* do a solr search */
    $results = null;
    try {
        $results = $solr->search($query, $first-1, $vbulletin->options['searchperpage'], $filters);
    } catch (Exception $e) {
        var_dump($e->getMessage());
    }
    
    $numitems = $results->response->numFound;
    
    $docs = $results->response->docs;
    $searchtime = $results->responseHeader->QTime / 1000;
    /* build a post/thread array */
    $post = array();
    $posts = array();
    foreach ($docs as $key => $doc) {
        $post['userid'] = $doc->p_userid;
        $post['pagetext'] = $doc->p_pagetext;
        $post['posttitle'] = $doc->p_title;
        $post['threadid'] = $doc->t_id;
        $post['postid'] = intval($doc->p_id);
        $post['threadtitle'] = $doc->t_title;
        $post['threadiconid'] = ''; //<--
        $post['dateline'] = $doc->t_dateline;
        $post['forumid'] = $doc->f_id;
        $post['sticky'] = ''; //<--
        $post['prefixid'] = ''; //<--
        $post['taglist'] = ''; //<--
        $post['pollid'] = ''; //<--
        $post['open'] = ''; //<--
        $post['postdateline'] = $doc->p_dateline;
        $post['visible'] = 1;
        $post['hiddencount'] = ''; //<--
        $post['deletedcount'] = ''; //<--
        $post['attach'] = ''; //<--
        $post['username'] = $doc->p_username;
        $post['replycount'] = $doc->t_replycount;
        $post['views'] = $doc->t_views;
        $post['lastpost'] = $doc->t_lastpost;
        $post['lastposter'] = $doc->t_lastposter;
        $post['lastpostid'] = $doc->t_lastpostid;
        $post['del_userid'] = ''; //<--
        $post['del_username'] = ''; //<--
        $post['del_reason'] = ''; //<--
        $post['postuserid'] = $doc->p_userid; //<--
        $post['issubscribed'] = ''; //<--
        $post['threadread'] = ''; //<--
        $post['forumtitle'] = $doc->f_title;
        $post['postdate'] = date('d.m.Y', $doc->t_dateline);
        $post['posttime'] = date('H:i', $doc->t_dateline);
        $post['lastpostdate'] = date('d.m.Y', $doc->t_lastpost);
        $post['lastposttime'] = date('H:i', $doc->t_lastpost);
        $post['highlight'] = '&amp;highlight='.$query;
        $post['posticon'] = 1;
        $post['posticonpath'] = 'images/icons/icon1.gif';
        $post['threadiconpath'] = 'images/icons/icon1.gif';
        $posts[] = $post;
        unset($post);
    }

    /* if something is found, display the reults based on display option chosen ( posts / threads ) */
    if ($results->response->numFound < 1) {
        eval(standard_error(fetch_error('searchnoresults_getnew', $vbulletin->session->vars['sessionurl']), '', false));
    } 
    /* stores unique threadids */
    $threadmemory = array();
    $threadadmin_imod_menu = '';
    
    /* find out which threads user subscribed */
    $sql = "SELECT threadid\n"
           ."FROM subscribethread\n"
           ."WHERE userid = " . $prefs['userid'];
    $ids = $db->query_read($sql);
    $subscribedthread = array();
    while ($id = $db->fetch_array($ids)) {
        $subscribedthread[] = $id['threadid'];
    }
    
    /* find out if user posted in a thread */
    $itemids = array();
    foreach ($posts as $key => $value) {
        $itemids[$value['threadid']] = true;
    }
    if (!empty($itemids)) {
        $threadids = implode(', ', array_keys($itemids));
    }
    
    if($search['showposts']) {        
        foreach ($posts as $key => $post) {
            $isnew = ($post['postdateline'] > $vbulletin->userinfo['lastvisit']);
            if ($isnew) {
                $post['post_statusicon'] = 'new';
                $post['post_statustitle'] = $vbphrase['unread'];
            } else {
                $post['post_statusicon'] = 'old';
                $post['post_statustitle'] = $vbphrase['old'];
            }
            eval('$searchbits .= "' . fetch_template('search_results_postbit') . '";');
            if ($show['popups'] AND $show['inlinemod']) {
                eval('$threadadmin_imod_menu = "' . fetch_template('threadadmin_imod_menu_post') . '";');
            }
        }
    } else {
        $dotthreads = fetch_dot_threads_array($threadids);
        $show['deletedthread'] = ($thread['deletedcount'] > 0 AND (can_moderate($thread['forumid']) OR $forumperms & $vbulletin->bf_ugp_forumpermissions['canseedelnotice'])) ? true : false;
        foreach ($posts as $key => $thread) {
            if (in_array($thread['threadid'], $threadmemory)){
                continue;
            } else {
                if (in_array($thread['threadid'], $subscribedthread)){
                    $show['subscribed'] = 1;
                } else {
                    $show['subscribed'] = 0;
                }
                $threadmemory[] = $thread['threadid'];
                if (array_key_exists($thread['threadid'], $dotthreads)) {
                    $show['dotthreads'] = 1;
                    $show['threadcount'] = 1;
                    $thread['statusicon'] = '_dot'; 
                    $thread['dot_count'] = $dotthreads[$thread['threadid']]['count'];
                    $thread['dot_lastpost'] = $dotthreads[$thread['threadid']]['lastpost'];
                }
                eval('$searchbits .= "' . fetch_template('threadbit') . '";');
            }
        }
        $numitems = count($threadmemory);
        if ($show['popups'] AND $show['inlinemod']) {
            eval('$threadadmin_imod_menu = "' . fetch_template('threadadmin_imod_menu_thread') . '";');
        }
    }               
    $templatename = 'search_results';

    $last = (($vbulletin->options['searchperpage'] * $vbulletin->GPC['pagenumber']) > $numitems) ? $numitems : $vbulletin->options['searchperpage'] * $vbulletin->GPC['pagenumber'];
}



$navbits = array('search.php' . $vbulletin->session->vars['sessionurl_q'] => $vbphrase['search_forums']);
$navbits[''] = $vbphrase['search_results'];
$newpagenumber = $vbulletin->GPC['pagenumber'] + 1;
$adress2 =  'showposts' . '=' . $search['showposts'] . '&amp;'  
            .'titleonly' . '=' . $prefs['titleonly'] . '&amp;'
            .'searchdate' . '=' . $prefs['searchdate'] . '&amp;'
            .'beforeafter' . '=' . $prefs['beforeafter'] . '&amp;'
            .'sortby' . '=' . $prefs['sortby'] . '&amp;'
            .'sortorder' . '=' . $prefs['sortorder'] . '&amp;'
            .'replyless' . '=' . $prefs['replyless'] . '&amp;'
            .'replylimit' . '=' . $prefs['replylimit'] . '&amp;'
            .'searchtype' . '=' . $prefs['searchtype'] . '&amp;'
            .'lastvisited' . '=' . $prefs['lastvisited'] . '&amp;'
            .'exactname' . '=' . $prefs['exactname'] . '&amp;'
            .'starteronly' . '=' . $prefs['starteronly'] . '&amp;'
            .'forumchoice' . '=' . $prefs['forumchoice'] . '&amp;'
            .'childforums' . '=' . $prefs['childforums'] . '&amp;'
            .'userid' . '=' . $prefs['userid'] . '&amp;';

$pagenav = construct_page_nav($vbulletin->GPC['pagenumber'], $vbulletin->options['searchperpage'], $numitems, 'solrsearch.php?do=process' . '&amp;', $adress2);
sanitize_pageresults($numitems, $vbulletin->GPC['pagenumber'], $vbulletin->GPC['perpage'], $vbulletin->options['maxresults'], $vbulletin->options['searchperpage']);    

$frmjmpsel['search'] = 'class="fjsel" selected="selected"';
construct_forum_jump();

if ($templatename != '') {
    $navbits = construct_navbits($navbits);
    eval('$navbar = "' . fetch_template('navbar') . '";');
    eval('print_output("' . fetch_template($templatename) . '");');
}