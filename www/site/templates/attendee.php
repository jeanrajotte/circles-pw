<?php

// attendee template

if ($input->urlSegment1 === 'save') {
	attendeeSave( $page, $input);
	$session->redirect($page->url, false);
}
$content = '<p class="generous bg-success">'
	.$page->title
	.' is registered now. You can change your info and press Save if you please.</p>'; 
$content .= attendeeForm( $page );

// if the rootParent (section) page has more than 1 child, then render 
// section navigation in the sidebar
if($page->rootParent->hasChildren > 1) {
	$sidebar = renderNavTree($page->rootParent, 1) . $page->sidebar; 
}
