	var loading_lexi_img = new Image(); 
	loading_lexi_img.src = lexi_url+'/wp-content/plugins/lexi/img/loading-page.gif';
	
	function lexi_feed( url, title, num, conf, rand, page )
	{
		var lexi_sack = new sack(lexi_url+'/wp-admin/admin-ajax.php' );
		
		//Our plugin sack configuration
		lexi_sack.execute = 0;
		lexi_sack.method = 'POST';
		lexi_sack.setVar( 'action', 'lexi_ajax' );
		lexi_sack.element = 'lexi'+rand;
		
		//The ajax call data
		lexi_sack.setVar( 'url', url );
		lexi_sack.setVar( 'title', title );
		lexi_sack.setVar( 'num', num );
		lexi_sack.setVar( 'conf', conf );
		lexi_sack.setVar( 'rand', rand );
		lexi_sack.setVar( 'page', page );
		
		//What to do on error?
		lexi_sack.onError = function() {
			var aux = document.getElementById(lexi_sack.element);
			aux.innerHTMLsetAttribute=lexi_i18n_error;
		};
		
		lexi_sack.onCompletion = function() {
			lexi_completion(rand);
		}
		
		lexi_sack.runAJAX();
		
		return true;

	} // end of JavaScript function lexi_feed
