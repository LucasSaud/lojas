// Scroll Suave

$(document).ready(function(){

	$('.smoothscroll a,.smoothscroll').click(function(){
	    $('html, body').animate({
	        scrollTop: $( $.attr(this, 'href') ).offset().top
	    }, 900);
	    return false;
	});

}); 