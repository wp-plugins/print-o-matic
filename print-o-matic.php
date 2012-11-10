<?php
/*
Plugin Name: Print-O-Matic
Plugin URI: http://plugins.twinpictures.de
Description: Shortcode that adds a printer icon, allowing the user to print the post or a specified HTML element in the post.
Version: 1.0
Author: Twinpictures
Author URI: http://twinpictuers.de
License: GPL2
*/

/*  Copyright 2012 Twinpictures (www.twinpictures.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function printMaticInit() {
	wp_enqueue_script('jquery');
	if (!is_admin()){
		//collapse script
		wp_register_script('printomatic-js', plugins_url('/printomat.js', __FILE__), array('jquery'), '1.0');
		wp_enqueue_script('printomatic-js');

		//css
		wp_register_style( 'printomatic-css', plugins_url('/css/style.css', __FILE__) , array (), '1.0' );
		wp_enqueue_style( 'printomatic-css' );
	}
	
	add_shortcode("print-me", "pom_print_trigger");
}
add_action('init', 'printMaticInit');

function pom_print_trigger($atts, $content = null){
	$ran = rand(1, 10000);
	extract(shortcode_atts(array(
		'id' => 'id'.$ran,
		'target' => 'article',
		'title' => ''
	), $atts));
		
	
	$output = "<div class='printomatic' id='".$id."' title='".$title."' ></div><input type='hidden' id='target-".$id."' value='".$target."' />";
	return  $output;
}


?>