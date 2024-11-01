<?php
/*
Plugin Name: SS Old URLs
Plugin URI: http://www.strangerstudios.com/wordpress-plugins/ss-old-urls/
Description: Update 404 to redirect URLs from old website to current location.
Version: 1.0
Author: Jason Coleman
Author URI: http://www.strangerstudios.com/
*/

	global $wpdb, $wp_oldurls;
	$ssor_db_version = "1.0";
	$wp_oldurls = $wpdb->prefix ."oldurls";
	
	function ssou_install()
	{
		global $wpdb;
		global $ssou_db_version;
		global $wp_oldurls;
		
		$table_name = $wp_oldurls;
		if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
		{		  
		  //our table
		  $sql = "CREATE TABLE " . $table_name . " (
		  	`oldurl` varchar(255) NOT NULL,
  			`newurl` varchar(255) NOT NULL,
			 PRIMARY KEY  (`oldurl`)
		  ) TYPE=MyISAM ;";
		
		  //need this to run the dbDelta to create table
		  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		  dbDelta($sql);
					
		  //incase we upgrade DB in the future
		  add_option("ssou_db_version", $ssou_db_version);		
		}
	}
	
	function checkForOldURL($uri = NULL)
	{
		global $wpdb, $wp_oldurls;
		//check for an old url
		if(!$uri)
			$uri = $_SERVER['REQUEST_URI'];
		
		$newurl = $wpdb->get_var("SELECT newurl FROM $wp_oldurls WHERE oldurl = '$uri' LIMIT 1");
		
		//how about urls with slashes on the end?
		if(!$newurl && substr($uri, strlen($uri) - 1, 1) == "/")
			$newurl = $wpdb->get_var("SELECT newurl FROM $wp_oldurls WHERE oldurl = '" . substr($uri, 0, strlen($uri) - 1) . "' LIMIT 1");
		elseif(!$newurl)
			$newurl = $wpdb->get_var("SELECT newurl FROM $wp_oldurls WHERE oldurl = '" . $uri . "/' LIMIT 1");
		
		if($newurl)
		{
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: ".$newurl);
			header("Connection: close");			
			exit(0);
		}
		else
			return false;
	}		
		
	function ssou_menu()
	{
		 add_options_page('SS Old URLs', 'Old URLs', 8, 'ssoldurls', 'ssou_options_page');
	}
	
	function ssou_options_page()
	{
		global $wpdb, $wp_oldurls;
		
		$oldurl = $_REQUEST['ssou_from'];
		$newurl = $_REQUEST['ssou_to'];
		$delete = $_REQUEST['delete'];
		
		if($oldurl && $newurl)
		{
			if($wpdb->query("INSERT INTO $wp_oldurls (oldurl, newurl) VALUES('" . $oldurl . "', '" . $newurl . "')"))
			{
				$msg = 1;
				$msgt = "Your redirect has been added successfully.";
			}
			else
			{
				$msg = -1;
				$msgt = "Error adding redirect.";
			}
		}
		
		if($delete)
		{
			if($wpdb->query("DELETE FROM $wp_oldurls WHERE oldurl = '" . $delete . "' LIMIT 1"))
			{
				$msg = 1;
				$msgt = "Redirect for " . $delete . " has been deleted successfully.";
			}
			else
			{
				$msg = -1;
				$msgt = "Error deleting redirect for " . $delete . ".";
			}
		}
		
		?>
		
			<?php
				if($msg)
				{
				?>
					<div id="message" class="<?php if($msg > 0) echo "updated fade"; else echo "error"; ?>"><p><?=$msgt?></p></div>
				<?php
				}
			?>
		
			<div class="wrap nosub">
				<div id="icon-link-manager" class="icon32"></div>
				<h2>Old URLs Redirection</h2>
				
				<?php /* don't need this any more, cause I'm awesome
				<p><strong>Important:</strong> Make sure that you have added this code <strong>&lt;?php checkForOldURL(); ?&gt;</strong> to the top of your 404 page, above the <strong>get_header();</strong> line.</p>
				*/ ?>
				<p><strong>Example:</strong> Enter <em>/about.html</em> and <em>/about/</em> to redirect <em>http://www.yoursite.com<strong>/about.html</strong></em> to <em>http://www.yoursite.com<strong>/about/</strong></em>.</p>
				
				<h3>New Redirect</h3>
				<form action="/wp-admin/options-general.php?page=ssoldurls" method="post">
					<input type="text" name="ssou_from" value="" /> to
					<input type="text" name="ssou_to" value="" />
					<input type="submit" value="Add" />
				</form>
				
				<?php $redirects = $wpdb->get_results("SELECT * FROM $wp_oldurls ORDER BY oldurl"); ?>				
				<h3><?=count($redirects);?> Redirects</h3>
				<p>
					<?php						
						if(!$redirects)
						{
						?>
							Use the form above to add your first redirect.
						<?php
						}
						
						foreach($redirects as $redirect)
						{
					?>
						<strong><?=$redirect->oldurl?></strong> to <strong><?=$redirect->newurl?></strong> <small>[<a href="/wp-admin/options-general.php?page=ssoldurls&delete=<?=urlencode($redirect->oldurl)?>">remove</a>]</small><br />
					<?php
						}
					?>
				</p>
				
			</div>
			
			<?php if($redirects) { ?>
				<h3>For Faster Redirects, Place the Following Code <strong>at the Top</strong> of Your .htaccess File</h3>
				<textarea rows="10" cols="120" onclick="this.focus(); this.select();"><?php
						echo "# BEGIN ssoldurls redirects\n";
						foreach($redirects as $redirect)
						{
							echo "redirect 301 " . $redirect->oldurl . " " . get_option("home") . $redirect->newurl . "\n";					
						}
						echo "# END ssoldurls redirects\n\n";
				?></textarea>
			<?php } ?>
		<?php
	}
	
	//this function calls checkForOldURL if a 404 status message is sent
	function ssou_status_filter($s)
	{
		if(strpos($s, "404"))
		{
			if(!checkForOldURL())
				return $s;			//there was no redirect
			else
				return false;		//we probably already redirected
		}
		else
			return $s;
	}
	
	add_filter('status_header', 'ssou_status_filter');			
	register_activation_hook(__FILE__,'ssou_install');
	add_action('admin_menu', 'ssou_menu');		
?>
