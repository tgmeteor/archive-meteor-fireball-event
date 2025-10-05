<!-- Code Javascript pour le site du planétarium -->

<!-- Fontion newWindow -->
function newWindow(URL, name, specs)  {
	var OpenWindow = window.open(URL, name, specs);
}

<!-- Fontion monContenu -->

<!-- Fontion preloadImage -->
function preloadImagesTmp004F6923() {
	if (document.images) {
		over_bloc_planetarium135 = newImage(/*URL*/'../../javascript/Images/bloc_planetarium134-over.gif');
		over_leplanetarium9 = newImage(/*URL*/'../../javascript/Images/onglets/theplanetarium_over.jpg');
		over_leplanetarium10 = newImage(/*URL*/'../../javascript/Images/onglets/nowshowing_over.jpg');
		over_leplanetarium11 = newImage(/*URL*/'../../javascript/Images/onglets/groups_over.gif');
		over_leplanetarium12 = newImage(/*URL*/'../../javascript/Images/onglets/educationalactivities_over.gif');
		over_leplanetarium13 = newImage(/*URL*/'../../javascript/Images/onglets/whatsup_over.jpg');
		over_leplanetarium15 = newImage(/*URL*/'../../javascript/Images/onglets/media_over.gif');
		over_leplanetarium16 = newImage(/*URL*/'../../javascript/Images/onglets/faq-en_over.gif');
	}
}

var preloadFlag = false;

function preloadImages() {
	if (document.images) {
		preloadImagesTmp004F6923();
		preloadFlag = true;
	}
}

function agrandirimage()
{
	image.height="30"
}

function reduireimage()
{
	image.height="15"
}

function popupprint(page,vWidth,vHeight,scroll) {
	siteWindow = window.open(page,'','width='+vWidth+',height='+vHeight+',scrollbars='+scroll+',top=0,left=0,status=no,toolbar=no,resizable=no,location=no,menubar=yes')
	siteWindow.focus()
}

function popup(page,vWidth,vHeight,scroll) {

	siteWindow = window.open(page,'','width='+vWidth+',height='+vHeight+',scrollbars='+scroll+',top=0,left=0,status=no,toolbar=no,resizable=no,location=no,menubar=no')
	siteWindow.focus()
}