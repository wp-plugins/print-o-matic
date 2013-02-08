/*!
 * Print-O-Matic v1.0.1
 * http://plugins.twinpictures.de/plugins/print-o-matic/
 *
 * Copyright 2012, Twinpictures
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
		var w = window.open('', 'PrintOMatic', 'scrollbars=yes');
		w.document.write(jQuery(target).html());
		
		jQuery(':input[name]', target).each(function() {
			//console.log(jQuery(this).attr('name') + ':' + jQuery(this).val() );
			jQuery('[name=' + jQuery(this).attr('name') +']', w.document.body).val(jQuery(this).val())
		})
		//title
		jQuery(w.document.head).append("<title>"+ document.title +"</title>");
		
		//stylesheet
		if(site_css){
			jQuery(w.document.head).append(jQuery("<link/>", 
				{ rel: "stylesheet", href: site_css, type: "text/css" }
			));    
		}
		
		if(custom_css){
			jQuery(w.document.head).append("<style>"+ custom_css +"</style>");
		}        
		w.print();
	});
	
});
