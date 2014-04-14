/*!
 * Print-O-Matic JavaScript v1.5.4
 * http://plugins.twinpictures.de/plugins/print-o-matic/
 *
 * Copyright 2013, Twinpictures
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, blend, trade,
 * bake, hack, scramble, difiburlate, digest and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

jQuery(document).ready(function() {
	
	jQuery('.printomatic, .printomatictext').click(function() {
		var id = jQuery(this).attr('id');
		var target = jQuery('#target-' + id).val();
		var w = window.open( "", "printomatic", "scrollbars=yes");

		//title
		//rot in hell, Internet Explorer
		if (!!navigator.userAgent.match(/Trident\/7\./)){
			w.document.title = "PrintOMatic";
		}
		else{
			
			jQuery(w.document.head).append("<title>"+ document.title +"</title>");
		}
		
		//stylesheet
		if ( typeof pom_site_css != 'undefined' && pom_site_css ){
			jQuery(w.document.body).append('<link rel="stylesheet" type="text/css" href="' + pom_site_css + '" />'); 
		}
		
		if ( typeof pom_custom_css != 'undefined' && pom_custom_css ){
			jQuery(w.document.body).append("<style>"+ pom_custom_css +"</style>");
		}
		
		if ( typeof pom_do_not_print != 'undefined' && pom_do_not_print ) {
			jQuery(pom_do_not_print).hide();
		}
		
		if ( typeof pom_html_top != 'undefined' && pom_html_top ){
			jQuery(w.document.body).append( pom_html_top );
		}
		
		//rot in hell, Internet Explorer
		if (!!navigator.userAgent.match(/Trident\/7\./)){
			jQuery(w.document.body).append( jQuery( target ).clone().html() );
		}
		else{
			jQuery(w.document.body).append( jQuery( target ).clone() );
		}
		
		if ( typeof pom_do_not_print != 'undefined' && pom_do_not_print ) {
			jQuery(pom_do_not_print).show();
		}
		
		if ( typeof pom_html_bottom != 'undefined' && pom_html_bottom ){
			jQuery(w.document.body).append( pom_html_bottom );
		}
		
		w.print();
		w.document.close();
		
	});
	
});
