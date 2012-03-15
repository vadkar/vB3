<?php
/**
 * http://www.indexdepot.com
 *
 * @author Vadims Karpuschkins
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
