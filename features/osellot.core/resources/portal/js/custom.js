// BG stuff
$(document).ready(function() {
	docHeight();
});

function docHeight(){
	var documentHeight = jQuery(document).height();
	$("#dotmesh").css({height:documentHeight});
	var wrapheight = $("#wrapper").css('height');
	var minfootheight = 120;
	var viewportheight = 0;
	if ($(window).height() < $(document).height()) {
		viewportheight = $(document).height()+20;
	} else {viewportheight = $(window).height()}
	var footheight = (viewportheight-$("#wrapper").height());
	if (footheight < minfootheight) footheight = minfootheight;
	var footoutheight = footheight + "px";
	$("#footout").css({top:wrapheight});
	$("#footout").css({height:footoutheight});
	var footerheight = (footheight-50) + "px";
	$("#footer").css({height:footerheight});
}
$(window).resize(function(){
	docHeight();
});