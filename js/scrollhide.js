$(document).ready(function(){
	var didScroll;
	var lastScrollTop = 0;
	var delta = 5;	
	var deltaTop = 200;
	var navbarHeight = $('#header').outerHeight();

	$(window).scroll(function(event){
		didScroll = true;
	});

	if($(window).width() <= 480) // only smartphone devices
	{
		setInterval(function() {
			if (didScroll) {
				hasScrolled();
				didScroll = false;
			}
		}, 250);
	}

	function hasScrolled() {
		var st = $(this).scrollTop();
		
		//console.log(navbarHeight);
		// Make sure they scroll more than delta
		if(Math.abs(lastScrollTop - st) <= delta)
			return;
		
		// If they scrolled down and are past the navbar, add class .nav-up.
		// This is necessary so you never see what is "behind" the navbar.
		if (st > lastScrollTop && st > navbarHeight){
			// Scroll Down
			//$('#header').removeClass('nav-down').addClass('nav-up');
			
			$('#header').css('top',"-"+navbarHeight+"px");
		} else {
			// Scroll Up
			if(Math.abs(lastScrollTop - st) > deltaTop || st == 0)
			{
				if(st + $(window).height() < $(document).height()) {
					$('#header').css('top',"0px");
					//$('#header').removeClass('nav-up').addClass('nav-down');
				}
			}
			else
			{
				return;
			}
		}
		
		lastScrollTop = st;
	}
});