function init() {
	tinyMCEPopup.resizeToInnerSize();
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
	
	var feed = document.getElementById('feed_panel');
	
	// who is active ?
	if (feed.className.indexOf('current') != -1) {
		var feedid = document.getElementById('feedtag').value;
    if (feedid != 0 ) {
			tagtext = "[lexi:" + feedid + "]";
    } else {
				tagtext = "[lexi]";
    }
    add_text = true;
	}

	
	if(add_text) {
		window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tagtext);
	}
	window.tinyMCEPopup.close();
}
