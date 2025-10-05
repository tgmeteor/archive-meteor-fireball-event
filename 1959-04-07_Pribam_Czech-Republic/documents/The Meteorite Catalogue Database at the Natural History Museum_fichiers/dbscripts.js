//alert('hi');
/*checks to see which browser version and kicks out IE3 */
var netsgood = 0; var goodIE = 0;
browserName = navigator.appName.substring(0,8);
browserVer = parseFloat(navigator.appVersion);
if (browserName == "Netscape" && browserVer >= 3) { netsgood = 1; }
if (browserName == "Microsof" && browserVer >= 4.0) { goodIE = 1; }
/* jump to new location from dropdown */
function jumpMenu(targ,selObj,restore,selInd){ //v3.0
	eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
	//if (restore) selObj.selectedIndex=selInd;
}
/* reset jump menu to original value */
var resetSelInd = 'undefined';	// initialise variable
function resetMenu() {
//alert('resetting menu: index = ' + resetSelInd);
	if (resetSelInd != 'undefined') {
		document.forms[0].searchtype.selectedIndex = resetSelInd;
		document.forms[0].searchtype.blur();
	}
}
/*  functions below need dbrecvars.js INCPAGED (NOT linked) into head of docs which call them */
/* insert record numbers */
if (document.startNo) var curRecNo = startNo;	
function doRecNos() {
	if (netsgood||goodIE) {
		document.write(curRecNo);
		curRecNo=curRecNo+1;
	}
}
/* insert nav icon */
function insertNav(direc,anch,source){
	path = 'http://www.nhm.ac.uk/generic/';
	if (typeof source != 'undefined') path = source;
	if (netsgood||goodIE) {
		if (noInList >= 20) document.write('<td align="right" valign="top"><a href="#'+ anch + '"><img border="0" src="' + path + direc + '.gif" width="21" height="21" alt="' + anch + '"></a></td>');
	}
}
