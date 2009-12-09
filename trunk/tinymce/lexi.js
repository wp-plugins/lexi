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
	
  var rss = document.getElementById('rss_panel');
	
	// who is active ?
  if (rss.className.indexOf('current') != -1) {
    var rsslink = document.getElementById('rsslink').value;
    var items = document.getElementById('rssitems')
    var rssitems = items.options[items.selectedIndex].value;
		var title="";
		if(getCheckedValue(document.Lexi.group1)==2) {
			title = ","+document.getElementById('rssowntitle').value;
		}

		var config = 0;

		if (document.getElementById('rsscache').checked) config = config + 1;
		if (document.getElementById('rsssc').checked) config = config + 2;
		if (document.getElementById('rssst').checked) config = config + 4;
		if (document.getElementById('rsstb').checked) config = config + 8;


    tagtext = "[lexi:" + config + "," + rsslink + title + "," + rssitems + "]";
    add_text = true;
  }

	
	if(add_text) {
		window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tagtext);
	}
	window.tinyMCEPopup.close();
}
