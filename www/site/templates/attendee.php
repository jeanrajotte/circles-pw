<?php

// attendee template

if ($input->urlSegment1 === 'save') {
	attendeeSave( $page, $input);
	$session->redirect($page->url, false);
}
$content = attendeeForm( $page );

// if the rootParent (section) page has more than 1 child, then render 
// section navigation in the sidebar
if($page->rootParent->hasChildren > 1) {
	$sidebar = renderNavTree($page->rootParent, 1) . $page->sidebar; 
}
