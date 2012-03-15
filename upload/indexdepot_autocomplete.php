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
error_reporting(E_ALL & ~E_NOTICE & ~8192);

require_once('./global.php');
require_once(DIR . '/includes/indexdepot_globals.php');

$term = isset($_REQUEST['term']) ? $_REQUEST['term'] : '';
$solr = indexdepot_solrsearch_connection($vbulletin);
indexdepot_suggest_collection($term, $solr);

function indexdepot_suggest_collection($term, $solr)
{
    $field = 'suggest';
    $term = trim(strtolower($term));
    $fq = '';

    $words = explode(' ', $term);
    $numWords = count($words);
    if ($numWords > 1) {
        $term = array_pop($words);
        $fq = trim(implode(' ', $words));
    }

    $limit = 10;
    $limit += $numWords - 1;

    $params = array(
        'start' => '0',
        'rows' => '0',
        'facet' => 'true',
        'facet.field' => 'suggest',
        'facet.mincount' => 1,
        'facet.limit' => $limit,
        'facet.sort' => 'count',
        'facet.prefix' => utf8_encode($term),
        'version' => '2.2',
        'indent' => 'on'
    );
    if (! empty($fq)) {
        $params['fq'] = $field . ':' . $fq;
        $fq = $fq . ' ';
    }
    $response = $solr->search('*:*', 0, 0, $params);

    $data = array();
    $suggests = $response->facet_counts->facet_fields->{$field};

    $fqTerms = explode(' ', $fq);

    $counter = 0;
    foreach ($suggests as $suggest => $count) {
        $suggestTerms = explode(' ', $suggest);
        $intersect = array_intersect($suggestTerms, $fqTerms);
        if (empty($intersect)) {
            $data[] = $fq . $suggest;
        }
    }
    array_splice($data, 10);
    
    $response = json_encode($data);
    echo $response;
}
