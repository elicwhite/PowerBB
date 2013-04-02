<?php
//----------------------------------------------
// PowerBB
//----------------------------------------------
// All code is copyright to Power Software
// unless mentioned otherwise. This code
// may NOT be reproduced, or distributed
// by any means, unless you have explicit
// written permission from Power Software.
// Some code is derived from early versions
// of PunBB.
//-----------------------------------------------
// Copyright as of 2006
// All rights reserved
//-----------------------------------------------

define('FORUM_ROOT', './');
require FORUM_ROOT.'include/common.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/map.php';
$page_title = convert_htmlspecialchars($configuration['o_board_name']." / ".$lang_map['Title']);
require FORUM_ROOT.'header.php';
?>
<div class="box block">
<h2><?php echo $lang_map['Title'] ?></h2>
<p><?php echo $lang_map['Desc'] ?> <?php echo $lang_map['Info'] ?></p>
<div id="map" style="height: 600px;"></div>
<script src="http://maps.google.com/maps?file=api&v=1&key=<?php echo convert_htmlspecialchars($configuration['o_um_key']) ?>" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
	<?php if (eregi('msie', $_SERVER['HTTP_USER_AGENT'])) echo '	window.onload = function() {'."\n"; ?>
	var map = new GMap(document.getElementById("map"));
	map.addControl(new GLargeMapControl());
	map.addControl(new GMapTypeControl());
	map.centerAndZoom(new GPoint(<?php echo $configuration['o_um_default_lng'] ?>, <?php echo $configuration['o_um_default_lat'] ?>), <?php echo convert_htmlspecialchars($configuration['o_um_default_zoom']) ?>);

	function createMarker(point, marker)
	{
		var marker;
  		var id = marker.getAttribute("id");
		var username = marker.getAttribute("username");
		var title = marker.getAttribute("title");
		var realname = marker.getAttribute("realname");
		var url = marker.getAttribute("url");
		var location = marker.getAttribute("location");
		if(marker.getAttribute("useavatar") == 1 && marker.getAttribute("avatar") != "")
		{
			var avatar = "<dd><img src=\""+marker.getAttribute("avatar")+"\" alt=\"\" /></dd>";
		}
		else
		{
			var avatar = '';
		}
		var marker = new GMarker(point);
		username = username.replace("<", "&lt;");
		username = username.replace(">", "&gt;");
		username = username.replace('"', "&quot;");
		realname = realname.replace("<", "&lt;");
		realname = realname.replace(">", "&gt;");
		realname = realname.replace('"', "&quot;");
		location = location.replace("<", "&lt;");
		location = location.replace(">", "&gt;");
		location = location.replace('"', "&quot;");
		var html = "<dl><dt><strong><a href=\"profile.php?id="+id+"\">"+username+"</a></strong></dt>"+avatar+"<dd>"+realname+"</dd><dd>"+location+"</dd></dl>";
		GEvent.addListener(marker, "click", function()
		{
		marker.openInfoWindowHtml(html); 
	});
	return marker;
}
var request = GXmlHttp.create();
request.open("GET", "markers.php", true);
request.onreadystatechange = function()
{
	if (request.readyState == 4)
	{
		var xmlDoc = request.responseXML;
		var markers = xmlDoc.documentElement.getElementsByTagName("marker");
		for (var i = 0; i < markers.length; i++)
		{
			var point = new GPoint(parseFloat(markers[i].getAttribute("lng")), parseFloat(markers[i].getAttribute("lat")));
			var marker = createMarker(point, markers[i])
			map.addOverlay(marker);
		}
	}
}
request.send(null);
<?php if (eregi('msie', $_SERVER['HTTP_USER_AGENT'])) echo '	}'; ?>
//]]>
</script>
</div>
<?php require FORUM_ROOT.'footer.php'; ?>