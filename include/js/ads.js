var perline = 9;
var divSet = false;
var curId;
var colorLevels = Array('0', '3', '6', '9', 'C', 'F');
var colorArray = Array();
var ie = false;
var nocolor = 'none';
if (document.all) { ie = true; nocolor = ''; }

function getObj(id)
{
	if (ie)
	{
		return document.all[id];
	} 
	else
	{	
		if(id.indexOf('_sample')>-1)
		{
			var eles = new Array();
			allspans = document.getElementsByTagName('span');
			alltds = document.getElementsByTagName('td');
			var span, i = 0;
			while (span = allspans.item(i++))
			{
					if (span.className == id && span.id == id)
					{
						eles[eles.length] = span;
					}
			}
			var td, i = 0;
			while (td = alltds.item(i++))
			{
				if (td.className == id && td.id == id) eles[eles.length] = td;
			}
			if(eles.length==1)
			{
				return eles[0];
			}
			return eles;
		}
		else
		{
			return document.getElementById(id);
		}
	}
}

function addColor(r, g, b)
{
	var red = colorLevels[r];
	var green = colorLevels[g];
	var blue = colorLevels[b];
	addColorValue(red, green, blue);
}

function addColorValue(r, g, b)
{
	colorArray[colorArray.length] = '#' + r + r + g + g + b + b;
}
   
function setColor(color)
{
	var link = getObj(curId);
	var field = getObj(curId + '_value');
	field.value = color;
	if(curId=='clicksor_banner_border')
	{
		getObj(curId+'_sample').style.background= color;
	}
	else if(curId=='clicksor_banner_ad_bg')
	{
		var objs = getObj(curId+'_sample');
		if(objs.length>1)
		{
			for(var i=0;i<objs.length;i++)
			{
			  objs[i].style.background = color;
			}
		}
		else
		{
			objs.style.background = color;
		}
	}
	else
	{
		var objs = getObj(curId+'_sample');
		if(objs.length>1)
		{
			for(var i=0;i<objs.length;i++)
			{
				objs[i].style.color = color;
			}
		}
		else getObj(curId+'_sample').style.color= color;
	}
	if (color == '')
	{
		link.style.background = nocolor;
		link.style.color = nocolor;
		color = nocolor;
	}
	else
	{
		link.style.background = color;
		link.style.color = color;
	}
	hidepicker();
	str = curId+'="'+color+'";';
	eval(str);
	update();
}

function hidepicker(n)
{
	var picker = getObj('colorpicker');
	picker.style.display = 'none';
	if(n==1)
	{
		getObj('clicksor_banner_border_sample').style.background = getObj('clicksor_banner_border_value').value;
		var objs = getObj('clicksor_banner_ad_bg_sample');
		if(objs.length>1)
		{
			for(var i=0;i<objs.length;i++)
			{
				objs[i].style.background = getObj('clicksor_banner_ad_bg_value').value;
			}
		}
		else getObj('clicksor_banner_ad_bg_sample').style.background= getObj('clicksor_banner_ad_bg_value').value;
		var objs = getObj('clicksor_banner_link_color_sample');
		if(objs.length>1)
		{
			for(var i=0;i<objs.length;i++)
			{
				objs[i].style.color = getObj('clicksor_banner_link_color_value').value;
			}
		}
		else getObj('clicksor_banner_link_color_sample').style.color= getObj('clicksor_banner_link_color_value').value;
		var objs = getObj('clicksor_banner_text_color_sample');
		if(objs.length>1)
		{
			for(var i=0;i<objs.length;i++)
			{
				objs[i].style.color = getObj('clicksor_banner_text_color_value').value;
			}
		}
		else getObj('clicksor_banner_text_color_sample').style.color= getObj('clicksor_banner_text_color_value').value;
	}
}

function setDiv()
{     
	if (!document.createElement) { return; }
	var elemDiv = document.createElement('div');
	if (typeof(elemDiv.innerHTML) != 'string') { return; }
	genColors();
	elemDiv.id = 'colorpicker';
	elemDiv.style.position = 'absolute';
	elemDiv.style.display = 'none';
	elemDiv.style.border = '#000000 1px solid';
	elemDiv.style.background = '#FFFFFF';
	elemDiv.innerHTML = '<table><tr><td><span style="font-family:Verdana; font-size:11px;">Pick a color:</span></td><td align="right"><a href="javascript:hidepicker(1)"><img src="../../img/admin/hide.gif" border="0"></a></td></tr>' 
		+ '<tr><td colspan="2">'+getColorTable() +'</td></tr></table>';
	document.body.appendChild(elemDiv);
	divSet = true;
}

function pickColor(id)
{
	if (!divSet) { setDiv(); }
	var picker = getObj('colorpicker');     	
	if (id == curId && picker.style.display == 'block')
	{
		picker.style.display = 'none';
		return;
	}
	curId = id;
	var thelink = getObj(id);
	picker.style.top = getAbsoluteOffsetTop(thelink) + 20;
	picker.style.left = getAbsoluteOffsetLeft(thelink);     
	picker.style.display = 'block';
}

function genColors()
{                      
	for (a = 1; a < colorLevels.length; a++) addColor(0,0,a);
	for (a = 1; a < colorLevels.length - 1; a++) addColor(a,a,5);
	for (a = 1; a < colorLevels.length; a++) addColor(0,a,0);
	for (a = 1; a < colorLevels.length - 1; a++) addColor(a,5,a);
	for (a = 1; a < colorLevels.length; a++) addColor(a,0,0);
	for (a = 1; a < colorLevels.length - 1; a++) addColor(5,a,a);
	for (a = 1; a < colorLevels.length; a++) addColor(a,a,0);
	for (a = 1; a < colorLevels.length - 1; a++) addColor(5,5,a);
	for (a = 1; a < colorLevels.length; a++) addColor(0,a,a);
	for (a = 1; a < colorLevels.length - 1; a++) addColor(a,5,5);
	for (a = 1; a < colorLevels.length; a++) addColor(a,0,a);			
	for (a = 1; a < colorLevels.length - 1; a++) addColor(5,a,5);
	return colorArray;
}
  
function getColorTable()
{
	var colors = colorArray;
	var tableCode = '';
	tableCode += '<table border="0" cellspacing="1" cellpadding="1">';
	for (i = 0; i < colors.length; i++)
	{
		if (i % perline == 0) { tableCode += '<tr>'; }
		tableCode += '<td bgcolor="#000000"><a style="outline: 1px solid #000000; color: ' 
			+ colors[i] + '; background: ' + colors[i] + ';font-size: 10px;" title="' 
			+ colors[i] + '" href="javascript:setColor(\'' + colors[i] + '\');"  onmouseover="showCurrentSample(\'' + colors[i] + '\');">&nbsp;&nbsp;&nbsp;</a></td>';
		if (i % perline == perline - 1) { tableCode += '</tr>'; }
	}
	if (i % perline != 0) { tableCode += '</tr>'; }
	tableCode += '</table>';
	return tableCode;
}
  
function showSelectSample()
{
	if(curId)
	{
		if(curId=='clicksor_banner_border')
		{
			getObj(curId+'_sample').style.background=getObj(curId+'_value').value;
		}
		else if(curId=='clicksor_banner_ad_bg')
		{
			var objs = getObj(curId+'_sample');
			for(var i=0;i<objs.length;i++)
			{
				objs[i].style.background = value;
			}
		}
		else
		{
			var objs = getObj(curId+'_sample');
			if(objs.length>1)
			{
				for(var i=0;i<objs.length;i++)
				{
					objs[i].style.color = value;
				}
			}
			else getObj(curId+'_sample').style.color= value;
		}
	}
}

function showCurrentSample(color)
{
	if(curId)
	{
		if(curId=='clicksor_banner_border')
		{
			getObj(curId+'_sample').style.background= color;
		}
		else if(curId=='clicksor_banner_ad_bg')
		{
			var objs = getObj(curId+'_sample');
			if(objs.length>1)
			{
				for(var i=0;i<objs.length;i++)
				{
					objs[i].style.background = color;
				}
			}
			else
			{
				objs.style.background = color;
			}
		}
		else
		{
			var objs = getObj(curId+'_sample');
			if(objs.length>1)
			{
				for(var i=0;i<objs.length;i++)
				{
					objs[i].style.color = color;
				}
			}
			else getObj(curId+'_sample').style.color= color;
		}
	}
}

function relateColor(id, color)
{
	curId = id;
	field = getObj(id+'_value');
	try
	{
		if(color.charAt(0)!='#') field.value = '#'+color;
		value = field.value;
		getObj(id).style.background = value;
		if(id=='clicksor_banner_border')
		{
			getObj(id+'_sample').style.background= value;
		}
		else if(curId=='clicksor_banner_ad_bg')
		{
			var objs = getObj(curId+'_sample');
			for(var i=0;i<objs.length;i++)
			{
				objs[i].style.background = value;
			}
		}
		else
		{
			var objs = getObj(curId+'_sample');
			if(objs.length>1)
			{
				for(var i=0;i<objs.length;i++)
				{
					objs[i].style.color = value;
				}
			}
			else getObj(curId+'_sample').style.color= value;
		}
	}
	catch(e)
	{
		getObj(id).style.background = "#ffffff";
		if(id=='clicksor_banner_border')
		{
			getObj(id+'_sample').style.background="#ffffff";
		}
		else if(curId=='clicksor_banner_ad_bg')
		{
			var objs = getObj(curId+'_sample');
			for(var i=0;i<objs.length;i++)
			{
				objs[i].style.background = "#ffffff";
			}
		}
		else
		{
			var objs = getObj(curId+'_sample');
			if(objs.length>1)
			{
				for(var i=0;i<objs.length;i++)
				{
					objs[i].style.color = "#ffffff";
				}
			}
			else getObj(curId+'_sample').style.color= "#ffffff";
		}
	}
	value = field.value;
	str = id+'="'+value+'";';
	eval(str);
	update();
}

function getAbsoluteOffsetTop(obj)
{
	var top = obj.offsetTop;
	var parent = obj.offsetParent;
	while (parent != document.body)
	{
		top += parent.offsetTop;
		parent = parent.offsetParent;
	}
	return top;
}

function getAbsoluteOffsetLeft(obj)
{
	var left = obj.offsetLeft;
	var parent = obj.offsetParent;
	while (parent != document.body)
	{
		left += parent.offsetLeft;
		parent = parent.offsetParent;
   	}
	return left;
}

var clicksor_banner_border='#B4D0DC';
var clicksor_banner_ad_bg = '#ECF8FF';
var clicksor_banner_link_color = '#0000CC';
var clicksor_banner_text_color = '#000000';
var clicksor_banner_image_banner = true;
var clicksor_enable_pop=false;
var clicksor_banner_float = false;

function imageBanner(obj)
{
	clicksor_banner_image_banner = obj.checked;
	update();
}

function poPowerBBder(obj){
	clicksor_enable_pop = obj.checked;
	update();
}

function update()
{
	if(clicksor_banner_border=='#' || clicksor_banner_border=='') clicksor_banner_border='#B4D0DC';
	if(clicksor_banner_ad_bg=='#' || clicksor_banner_ad_bg=='') clicksor_banner_ad_bg='#ECF8FF';
	if(clicksor_banner_link_color=='#' || clicksor_banner_link_color=='') clicksor_banner_link_color='#0000CC';
	if(clicksor_banner_text_color=='#' || clicksor_banner_text_color=='') clicksor_banner_text_color='#000000';
}