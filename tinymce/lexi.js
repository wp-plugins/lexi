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
	
	var lexi = document.getElementById('lexi_panel');
  var rss = document.getElementById('rss_panel');
	
	// who is active ?
	if (lexi.className.indexOf('current') != -1) {
		var lexiid = document.getElementById('lexiid').value;
    if (lexiid != 0 ) {
			tagtext = "[lexi:" + lexiid + "]";
    } else {
				tagtext = "[lexi]";
    }
    add_text = true;
	}
  if (rss.className.indexOf('current') != -1) {
    var rsslink = document.getElementById('rsslink').value;
    var items = document.getElementById('rssitems')
    var rssitems = items.options[items.selectedIndex].value;
    var rsssc = false;
    if (document.getElementById('rsssc').checked) rsssc = true;
    var rsscache = false;
    if (document.getElementById('rsscache').checked) rsscache = true;

    tagtext = "[lexi:" + rsslink + "," + rssitems + "," + rsssc + "," + rsscache + "]";
    add_text = true;
  }

	
	if(add_text) {
		window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tagtext);
	}
	window.tinyMCEPopup.close();
}
