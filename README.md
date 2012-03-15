## Installation

* Upload the upload folder to your vBulletin 3 Server
* Install the product-indexdepot.xml plugin
* Upload the schema.xml to your Solr Server
* Change your templates
    * **navbar line 198:** 
	`<form action="search.php?do=process" method="post">` to `<form action="indexdepot_solrsearch.php?do=process" method="post">`
	* **navbar line 206:** 
	`<input type="text" class="bginput" name="query" size="25" tabindex="1001" />` to `<input id="autocomplete" type="text" class="bginput" name="query" size="25" tabindex="1001" />`
	* **search_forums line 31:** 
	`<form action="search.php?do=process" method="post" name="vbform" id="searchform" style="display:block; margin:0px">` to `<form action="indexdepot_solrsearch.php?do=process" method="post" name="vbform" id="searchform" style="display:block; margin:0px">`
	* **search_forums line 55:** 
	`<input type="text" class="bginput" name="query" size="35" value="$query" style="width:250px" />` to `<input id="autocomplete" type="text" class="bginput" name="query" size="35" value="$query" style="width:250px" />`
	* **headinclude from line 25 add:**
	
	    <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/smoothness/jquery-ui.css" type="text/css" media="screen" />
	    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
	    <script>
	    $(document).ready(function() {
	        $("input#autocomplete").autocomplete({
	            source: "indexdepot_autocomplete.php"
	        });
	    });
	    </script>
	
* Go to vb/admincp/options and change the **Indexdepot Search Options**

### If you do not have a Solr Server you can visit us at [www.indexdepot.com](https://www.indexdepot.com/en/ "Visit us for your o0wn Solr Index") to get your own Solr Index
