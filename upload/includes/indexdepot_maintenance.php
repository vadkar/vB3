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
function indexdepot_maintenance($vbulletin, $vbphrase)
{
    if ($_REQUEST['do'] == 'chooser') {
        print_form_header('indexdepot_index_alldata', 'index');
        print_table_header($vbphrase['indexdepot_maintenance']);
        print_submit_row($vbphrase['indexdepot_rebuild_search_index']);
    }
}