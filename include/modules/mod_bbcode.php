<?php
if (!isset($bbcode_form)) $bbcode_form = 'post';
if (!isset($bbcode_field)) $bbcode_field = 'req_message';
?>
<script type="text/javascript">
<!--
	function insert_text(open, close)
	{
		msgfield = (document.all) ? document.all.req_message : document.forms['<?php echo$bbcode_form ?>']['<?php echo$bbcode_field ?>'];
		if (document.selection && document.selection.createRange)
		{
			msgfield.focus();
			sel = document.selection.createRange();
			sel.text = open + sel.text + close;
			msgfield.focus();
		}
		else if (msgfield.selectionStart || msgfield.selectionStart == '0')
		{
			var startPos = msgfield.selectionStart;
			var endPos = msgfield.selectionEnd;
			msgfield.value = msgfield.value.substring(0, startPos) + open + msgfield.value.substring(startPos, endPos) + close + msgfield.value.substring(endPos, msgfield.value.length);
			msgfield.selectionStart = msgfield.selectionEnd = endPos + open.length + close.length;
			msgfield.focus();
		}
		else
		{
			msgfield.value += open + close;
			msgfield.focus();
		}
		return;
	}
-->
</script>
<script type='text/javascript'>
function cascade(elem)
{
	etat=document.getElementById(elem).style.display;
	if(etat=="none"){document.getElementById(elem).style.display="block";}
	else{document.getElementById(elem).style.display="none";}
}
function tag_url()
{
	var FoundErrors = '';
	var enterURL = prompt("Enter URL", "http://");
	var enterTITLE = prompt("Enter title", "Click me");
	if (!enterURL)
	{
		FoundErrors += " " + "Please enter the URL";
	}
	if (!enterTITLE) {
		FoundErrors += " " + "Please enter the title";
	}

	if (FoundErrors) {
		alert("Error! "+FoundErrors);
		return;
	}
	insert_text("[url="+enterURL+"]"+enterTITLE+"[/url]", "", false);
}

function tag_mail()
{
	var FoundErrors = '';
	var enterADRESSE = prompt("Enter address", "");
	var enterNOM = prompt("Enter name", "Name");
	if (!enterADRESSE) {
		FoundErrors += " " + "Please enter address";
	}
	if (!enterNOM) {
		FoundErrors += " " + "Please enter name";
	}
	if (FoundErrors) {
		alert("Error! "+FoundErrors);
		return;
	}
	insert_text("[email="+enterADRESSE+"]"+enterNOM+"[/email]", "", false);
}

function tag_img()
{
	var FoundErrors = '';
	var enterIMG = prompt("Enter image address", "http://");
	if (!enterIMG) {
		FoundErrors += " " + "Please enter URL";
	}
	if (FoundErrors) {
		alert("Error! "+FoundErrors);
		return;
	}
	insert_text("[img]"+enterIMG+"[/img]", "", false);
}
var state = 'none';

function showhide(layer_ref)
{
	if (state == 'block')
	{
		state = 'none';
	}
	else
	{
		state = 'block';
	}
	if (document.all)
	{
		eval( "document.all." + layer_ref + ".style.display = state");
	}
	if (document.layers)
	{
		document.layers[layer_ref].display = state;
	}
	if (document.getElementById &&!document.all)
	{
		hza = document.getElementById(layer_ref);
		hza.style.display = state;
	}
}
</script>
<?php
if ($configuration['p_message_bbcode'] == '1')
{
?>
<div class="blockform" style="width:600px;">
	<input class="buttonb" type="button" name="B" onclick="insert_text('[b]','[/b]')" /> 
	<input class="buttoni" type="button" name="I" onclick="insert_text('[i]','[/i]')" />
	<input class="buttonu" type="button" name="U" onclick="insert_text('[u]','[/u]')" />
	<input class="buttons" type="button" name="S" onclick="insert_text('[s]','[/s]')" />
	<input class="buttonh" type="button" name="H" onclick="insert_text('[h]','[/h]')" />
	<input class="buttonaleft" type="button" name="ALIGNL" onclick="insert_text('[align=left]','[/align]')" />
	<input class="buttonacenter" type="button" name="ALIGNC" onclick="insert_text('[align=center]','[/align]')" />
	<input class="buttonaright" type="button" name="ALIGNR" onclick="insert_text('[align=right]','[/align]')" />
	<input class="buttonurl" type="button" name="Url" onclick="tag_url()"  />
	<input class="buttonemail" type="button" name="EMAIL" onclick="tag_mail()"  />
<?php
if ($configuration['p_message_img_tag'] == '1')
{
?>
	<input class="buttonimg" type="button" name="Img" onclick="tag_img()"  />
<?php
}
?>
	<input class="buttoncode" type="button" name="Code" onclick="insert_text('[code]','[/code]')" />
	<input class="buttonquote" type="button" name="Quote" onclick="insert_text('[quote]','[/quote]')" />

<?php
}
if ($configuration['p_is_upload'] == '1')
{
?>
	<input class="buttonis" type="button" name="ImageShack" onclick="showhide('div_imageshack');"  />
<?php
}
?>
</div>
