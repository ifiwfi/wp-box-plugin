<?php
error_reporting(E_ALL);

add_action('init', 'myStartSession', 1);
add_action('wp_logout', 'myEndSession');
add_action('wp_login', 'myEndSession');

function myStartSession() {
    if(!session_id()) {
        session_start();
    }
}

function myEndSession() {
    session_destroy ();
}

function template_redirect_intercept() {
    global $wp_query;
	$page_title = '';

    if(@array_key_exists('api', $wp_query->query_vars)){

		//You can do your magic here if needed before pageload.

		$page_title = 'BOX';
		
		//If you are using any specific header for BOX pages you can load that this way
		$current_template = TEMPLATEPATH.'/metronic/' ;
		if(file_exists($current_template.'header.php'))
			include_once($current_template.'header.php');
		else
			get_header();
?>
	<div class="row">
		<div class="col-md-12">
			<?php
				include('api/tree.php');
			?>
		</div>
	</div>
<?php
	if(file_exists($current_template.'footer.php'))
		include_once($current_template.'footer.php');
	else
		get_footer();

		exit;
    }
}
	add_action( 'template_redirect', 'template_redirect_intercept');
?>
