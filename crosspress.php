<?php
/*
Plugin Name: CrossPress
Plugin URI: http://www.atthakorn.com/project/crosspress
Description: Automatically, cross-posting to assiciated site/blog to enabling the post-via-email option with PIN code e.g. multiply.com, livejournal.com, blogspot.com and much more
Version: 0.3
Author: Atthakorn Chanthong
Author URI: http://www.atthakorn.com
*/

/*  Copyright 2008  Atthakorn Chanthong  (email : atthakorn@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class CrossPress
{
	var $saved = false;

	function CrossPress()
	{
		
		
		if($_POST['pin'] || $_POST['signature'] || $_POST['summarytext']) {

			if (get_option('crosspress_pin') || get_option('crosspress_pin') == NULL) {
				update_option('crosspress_pin',  $_POST['pin']);
			}
			else {
				add_option('crosspress_pin',  $_POST['pin']);
			}

			if (get_option('crosspress_signature') || get_option('crosspress_signature') == NULL) {
				update_option('crosspress_signature',  $_POST['signature']);
			}
			else {
				add_option('crosspress_signature',  $_POST['signature']);
			}
			
			if (get_option('crosspress_summary') || get_option('crosspress_summary') == NULL) {
				update_option('crosspress_summary', $_POST['summarytext']);
			}
			else {
				add_option('crosspress_summary',  $_POST['summarytext']);
			}

			$this->saved = true;
		}
		
		add_action('admin_menu', array(&$this, 'admin_menu'));

		
	}

	function admin_menu () {
		add_options_page('CrossPress Options', 'CrossPress', 8, __FILE__, array(&$this, 'plugin_options'));
	}

	function plugin_options () {
		
		if($this->saved) {
			print "<div id=\"message\" class=\"updated fade\"><p><strong>Options saved.</strong></p></div>\n\n";
		}
		
		print '<div class="wrap">';
		print '<h2>CrossPress Options</h2>';
		print '<hr>';
		print'<form style="padding-left:25px;" method="post" action="">';
		
		print '<p>';
		//print 'Secret Pin: <input name="pin" type=\"text\" size="50" value="'.stripcslashes(get_option('crosspress_pin')).'"><br>';
		//print 'Signature : <input name="signature" type=\"text\" size="50" value="'.stripcslashes(get_option('crosspress_signature')).'"><br>';
		print '<b>Secret PIN:</b><br>';
		print 'Site PIN code (email) that is enable you to cross-post to your associated site/blog via email.<br>';
		print 'Each entry separated by new line.<br>';
		print '<textarea name="pin"  cols="50" rows="5">'.stripcslashes(get_option('crosspress_pin')).'</textarea><br><br>';
		print '<b>Signature :</b><br>';
		print 'You can put a signature here as desire. The text url will be converted to clickable url automatically.<br>';
		print '<textarea name="signature" cols="50" rows="5">'.stripcslashes(get_option('crosspress_signature')).'</textarea><br><br>';
		//print '<input name="summarytext" type="checkbox" value="'.(strcmp(get_option('crosspress_summary'),"yes")?  "yes":  "no").'" '.(strcmp(get_option('crosspress_summary'),"yes")?  "checked":  "").'> Show summary text.';
		print '<input name="summarytext" type="checkbox" value="1" '.(get_option('crosspress_summary')=="1"?  "checked":  "").'> Show summary text.';
		print '</p>';
		print '<p><input type="submit" value="Save &raquo;"></p>';
		
		print'</form></div>';
		print '<br>';
		print '<br>';
		//print $this->getValidAddress(get_option("crosspress_pin"));
		
	}

	function add_action() {
		add_action('publish_post', array(&$this, 'post_2_blog'));
		//add_action('save_post', array(&$this, 'post_2_blog'));
	}

 	function post_2_blog($postid) {
	
		$to   = htmlspecialchars($this->getValidAddress(get_option("crosspress_pin")));
		$post = get_post($postid);
		
		//If post time is not equally to modified time, skip sending mail
		if ($post->post_date == $post->post_modified)
		{			
			$subject = "=?UTF-8?B?".base64_encode($post->post_title)."?=";
			
			if (get_option('crosspress_summary') == "1")
			{

				if (strlen($post->post_content) < 2000)
				{
					$msg = $post->post_content;
				} else
				{
					$msg = substr($post->post_content,0,1999);
					$msg .= '  <a href="'.$post->guid.'">[...]</a>';
				}
			}
			else
			{

				$msg = $post->post_content;
			}

			
			$msg .= ' '.'<br>';
			$msg .= ' '.'<br>';
			$msg .= make_clickable(stripcslashes(get_option("crosspress_signature")));

			$headers = "Content-Type: text/html;charset=utf8";
			
			//sending mail
			mail($to, $subject, $msg, $headers);
		}
	
		return $postid;
	}
	
	function getValidAddress($list)
	{
		$list = nl2br($list);
		$order = array('<br>', '<br/>', '<br />');
		$replace = ',';
		//remove new line
		$list = str_replace($order,$replace,$list);
		//clear white space
		$list = str_replace(' ','', $list);
		return $list;
	}



}

$post2blog =& new CrossPress;
$post2blog->add_action();

?>
