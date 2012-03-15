<?php
/**
 * http://www.indexdepot.com
 *
 * @author Vadims Karpuschkins
 */
function indexdepot_maintenance($vbulletin, $vbphrase)
{
    if ($_REQUEST['do'] == 'chooser') {
        print_form_header('indexdepot_index_alldata', 'index');
        print_table_header($vbphrase['indexdepot_maintenance']);
        print_submit_row($vbphrase['indexdepot_rebuild_search_index']);
    }
}