<?php

class Routes {

    function activate() {
        global $wp_rewrite;
        $this->flush_rewrite_rules();
    }

    // Took out the $wp_rewrite->rules replacement so the rewrite rules filter could handle this.
    function create_rewrite_rules($rules) {
        global $wp_rewrite;
        $newRule = array('api/(.+)' => 'index.php?api='.$wp_rewrite->preg_index(1));
        $newRules = $newRule + $rules;
        return $newRules;
    }

    function add_query_vars($qvars) {
        $qvars[] = 'api';
        return $qvars;
    }

    function flush_rewrite_rules() {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }
}

$RoutesCode = new Routes();
register_activation_hook( __file__, array($RoutesCode, 'flush_rewrite_rules') );

// Using a filter instead of an action to create the rewrite rules.
// Write rules -> Add query vars -> Recalculate rewrite rules
add_filter('rewrite_rules_array', array($RoutesCode, 'create_rewrite_rules'));
add_filter('query_vars',array($RoutesCode, 'add_query_vars'));

// Recalculates rewrite rules during admin init to save resourcees.
// Could probably run it once as long as it isn't going to change or check the
// $wp_rewrite rules to see if it's active.
add_filter('admin_init', array($RoutesCode, 'flush_rewrite_rules'));

