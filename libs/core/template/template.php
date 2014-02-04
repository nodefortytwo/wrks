<?php

function template_init(){
	Template::addCss('css/bootstrap.min.css', null, 'global');
	Template::addCss('css/bootstrap-responsive.min.css', null, 'global');
	Template::addCss('css/core.css', null, 'global');

	
	Template::addJs('js/jquery.js', null, 'global');
	Template::addJs('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false', null, 'global');
	Template::addJs('js/jquery.ui.map.full.min.js', null, 'global');
	//Template::addJs('//cdnjs.cloudflare.com/ajax/libs/modernizr/2.6.2/modernizr.min.js', null, 'global');
	Template::addJs('js/bootstrap.min.js', null, 'global');
	//Template::addJs('js/handlebars.js', null, 'global');
	//Template::addJs('js/ember.js', null, 'global');
	//Template::addJs('js/ember-data-latest.min.js', null, 'global');
	//Template::addJs('//cdnjs.cloudflare.com/ajax/libs/moment.js/2.0.0/moment.min.js', null, 'global');
	Template::addJs('js/holder.js', null, 'global');
	//Template::addJs('js/hammer.min.js', null, 'global');
	//Template::addJs('js/jquery.hammer.min.js', null, 'global');
	//Template::addJs('js/scrollfix.js', null, 'global');

	Template::addVariable('site_name', Config::get('site_name', 'Project Title'));
}