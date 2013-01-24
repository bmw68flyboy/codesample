jQuery.noConflict();
jQuery(document).ready(function() { 
	jQuery('.demo').hide(); // this is for demo purposes only. you can disregard this line.
		// SUPERFISH
	jQuery('#page-menu ul').superfish({
		hoverClass:  'over', 						// the class applied to hovered list items 
		delay:       400,                            // one second delay on mouseout 
		animation:   {opacity:'show',height:'show'},  // fade-in and slide-down animation 
		speed:       150,                          // faster animation speed 
		autoArrows:  true,                           // disable generation of arrow mark-up 
		dropShadows: true                            // disable drop shadows 
	}); 
	
	//toggle functions
	jQuery('.toggle-content').hide();
		//switch plus with minus image
	jQuery("h4.toggle").toggle(function(){
		jQuery(this).addClass("active");
		}, function () {
		jQuery(this).removeClass("active");
	});			
	//slide up and down on click
	jQuery("h4.toggle").click(function(){
		jQuery(this).next(".toggle-content").slideToggle();
	});
	
	jQuery('#small-menu ul').superfish({
		hoverClass:  'over', 						// the class applied to hovered list items 
		delay:       400,                            // one second delay on mouseout 
		animation:   {opacity:'show',height:'show'},  // fade-in and slide-down animation 
		speed:       150,                          // faster animation speed 
		autoArrows:  true,                           // disable generation of arrow mark-up 
		dropShadows: true                            // disable drop shadows 
	}); 
	
	jQuery('#main-menu ul').superfish({
		hoverClass:  'over', 						// the class applied to hovered list items 
		delay:       400,                            // one second delay on mouseout 
		animation:   {opacity:'show',height:'show'},  // fade-in and slide-down animation 
		speed:       150,                          // faster animation speed 
		autoArrows:  true,                           // disable generation of arrow mark-up 
		dropShadows: true                            // disable drop shadows 
	}); 
			
	jQuery('#post-1').click(function() { 
		jQuery('#slider1').cycle(0); 
		return false; 
	});
	jQuery('#post-2').click(function() { 
		jQuery('#slider1').cycle(1); 
		return false; 
	});
	jQuery('#post-3').click(function() { 
		jQuery('#slider1').cycle(2); 
		return false; 
	});
	jQuery('#post-4').click(function() { 
		jQuery('#slider1').cycle(3); 
		return false; 
	});
	jQuery('#post-5').click(function() { 
		jQuery('#slider1').cycle(4); 
		return false; 
	});
	jQuery('#post-6').click(function() { 
		jQuery('#slider1').cycle(5); 
		return false; 
	});
	
	//jQuery tabs
	jQuery('#tabbed-posts > ul').tabs({ fx: { height: 'toggle', opacity: 'toggle', duration: 100 } });
	jQuery('#tabbed-archives > ul').tabs({ fx: { height: 'toggle', opacity: 'toggle', duration: 100 } });
	jQuery('#tabbed-product-reviews > ul').tabs({ fx: { height: 'toggle', opacity: 'toggle', duration: 100 } });
	jQuery('#tabbed-movie-reviews > ul').tabs({ fx: { height: 'toggle', opacity: 'toggle', duration: 100 } });
	jQuery('#tabbed-music-reviews > ul').tabs({ fx: { height: 'toggle', opacity: 'toggle', duration: 100 } });
	jQuery('#tabbed-game-reviews > ul').tabs({ fx: { height: 'toggle', opacity: 'toggle', duration: 100 } });
	jQuery('#tabbed-book-reviews > ul').tabs({ fx: { height: 'toggle', opacity: 'toggle', duration: 100 } });
	jQuery('#tabbed-latest-reviews > ul').tabs({ fx: { height: 'toggle', opacity: 'toggle', duration: 100 } });
	jQuery('#tabbed-highest-rated-reviews > ul').tabs({ fx: { height: 'toggle', opacity: 'toggle', duration: 100 } });
	jQuery('.tabs-shortcode > ul').tabs({ fx: { height: 'toggle', opacity: 'toggle', duration: 100 } });
	
	if(!jQuery.browser.msie){button_hover_shortcode();}	
	
});

//BRIGHTEN AND DARKEN IMAGE HOVERS (OR ANY OTHER ELEMENT)
jQuery(function() {
	// BRIGHTEN
	// OPACITY OF BUTTON SET TO 75%
	jQuery(".brighten").css("opacity","0.75");			
	// ON MOUSE OVER
	jQuery(".brighten").hover(function () {											  
		// SET OPACITY TO 100%
		jQuery(this).stop().animate({
		opacity: 1.0
		}, 70);
	},			
	// ON MOUSE OUT
	function () {				
		// SET OPACITY BACK TO 75%
		jQuery(this).stop().animate({
		opacity: 0.75
		}, 700);
	});
	
	// DARKEN
	// ON MOUSE OVER
	jQuery(".darken img").hover(function () {											  
		// SET OPACITY TO 100%
		jQuery(this).stop().animate({
		opacity: .28
		}, 150);
	},			
	// ON MOUSE OUT
	function () {				
		// SET OPACITY BACK TO 75%
		jQuery(this).stop().animate({
		opacity: 1.0
		}, 600);
	});
});

//toggle the breaking panel
function showbreaking() {
	jQuery('#breaking-wrapper').animate({				 
		 height: 'toggle'				 
		}, 200, 'linear' );			
}

//toggle the demo settings - you can disregard this, it is only used for the Continuum demo
function showdemo() {
	jQuery('.demo').animate({				 
		 height: 'toggle'				 
		}, 200, 'linear' );			
}

//font replacement
Cufon.replace('.adelle, .post-content h1, .post-content h3', {fontFamily: 'Adelle'});
Cufon.replace('.gentesque, .post-content h2, .post-content h4, .category-link a, .demo-wrapper .header', {fontFamily: 'Gentesque'});

//button hovers
function button_hover_shortcode(){
	jQuery('.button_link,button[type=submit],button,input[type=submit],input[type=button],input[type=reset]').hover(
		function() {
				jQuery(this).stop().animate({opacity:0.85},150);
			},
			function() {
				jQuery(this).stop().animate({opacity:1},150);
		});
}
