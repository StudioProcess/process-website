<?php

/* DEBUG STUFF */
function hlog($x, $title="") {
	echo '<!-- ' . $title . ' --' . PHP_EOL;
	print_r($x);
	echo PHP_EOL . '-->' . PHP_EOL;
}

// query vars that will be extracted from GET, POST and permalink parsing
// args: array of query vars
// add_filter( 'query_vars', 'debug_query_vars', 99 );
function debug_query_vars( $qvars ) {
	hlog($qvars, 'query_vars');
	return $qvars;
}


//add_filter('post_rewrite_rules', 'debug_post_rewrite_rules');
function debug_post_rewrite_rules( $rules ) {
	hlog($rules, 'post_rewrite_rules');
	//hlog( count($rules) );
	return $rules;
}

//add_filter('root_rewrite_rules', 'debug_root_rewrite_rules');
function debug_root_rewrite_rules( $rules ) {
	hlog($rules, 'root_rewrite_rules');
	//hlog( count($rules) );
	return $rules;
}

//add_filter('page_rewrite_rules', 'debug_page_rewrite_rules');
function debug_page_rewrite_rules( $rules ) {
	hlog($rules, 'page_rewrite_rules');
	//hlog( count($rules) );
	return $rules;
}

// runs after all rewrite rules have been generated (need to call flush_rewrite_rules())
// args: WP_Rewrite object
// add_filter( 'generate_rewrite_rules', 'debug_generate_rewrite_rules', 99 );
function debug_generate_rewrite_rules( $r ) {
	hlog($r, 'generate_rewrite_rules');
	//hlog( count($r->rules) );
	return $r;
}

// the rules array from inside the WP_Rewrite object (need to call flush_rewrite_rules())
// args: array( pattern -> rewritten url)
// add_filter('rewrite_rules_array', 'debug_rewrite_rules_array');
function debug_rewrite_rules_array( $rules ) {
	hlog($rules, 'rewrite_rules_array');
	hlog( count($rules) );
	return $rules;
}
// flush_rewrite_rules();

// modify query vars before database query is run
// args: array( query_var => value )
// add_filter( 'request', 'debug_request', 99 );
function debug_request( $req ) {
	hlog($req, 'request');
	return $req;
}

// modify query vars. is_ variables are already set
// args: WP_Query object
// add_filter( 'pre_get_posts', 'debug_pre_get_posts', 99 );
function debug_pre_get_posts( $query ) {
	if ( $query->is_main_query() ) { // otherwise will use other queries like nav menus
		hlog($query, 'pre_get_posts');
	}
}

// override default template file choice
add_filter( 'template_include', 'debug_print_template', 99 );
function debug_print_template( $template ) {
	echo '<!-- Template: ' . basename($template) .' (' . $template . ')'. ' -->';
	return $template;
}

?>
