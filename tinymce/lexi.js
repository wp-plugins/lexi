function init() {
	tinyMCEPopup.resizeToInnerSize();
}

function is_numeric( cadena ) {
		var answer=false;
		var filter=/^([\d]+)$/;
		if (filter.test(cadena)) {
			answer=true;
		}
		return answer;
	}

function getCheckedValue(radioObj) {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}


function insertLexiLink() {
	
	var tagtext;
	var add_text = false;

	var rss = document.getElementById('rss_panel');

	// who is active ?
	if(rss.className.indexOf('current') != -1) {
		var rsslink = document.getElementById('rsslink').value;
		//Replace commas
		rsslink = rsslink.replace(/,/gi, '%2C');
		var items = document.getElementById('rssitems').value;
		if(!is_numeric(items)) items = 5;
		//var rssitems = items.options[items.selectedIndex].value;
		var title="";
		if(getCheckedValue(document.Lexi.group1)==2) {
			title = ","+document.getElementById('rssowntitle').value;
		}

		var config = 0;

		if (document.getElementById('rsscache').checked) config = config + 1;
		if (document.getElementById('rssst').checked) config = config + 4;
		if (document.getElementById('rsstb').checked) config = config + 8;
		if (!(document.getElementById('rssimg').checked)) config = config + 16;
		if (document.getElementById('rsssa').checked) config = config + 32;
		if (document.getElementById('rsssd').checked) config = config + 64;
		if (document.getElementById('rsspaginate').checked) config = config + 128;
		if (!(document.getElementById('rsssit').checked)) config = config + 256;
		
		switch(document.getElementById('content').value) {
			case "1":
				config = config + 2;
				break;
			case "2":
				config = config + 2 + 1024;
				break;
			case "3":
				config = config + 512 + 1024;
				break;
		}

		tagtext = "[lexi:" + config + "," + rsslink + title + "," + items + "]";
		add_text = true;
	}

	
	if(add_text) {
		window.tinyMCEPopup.execCommand('mceInsertContent', false, tagtext);
	}
	window.tinyMCEPopup.close();
}
