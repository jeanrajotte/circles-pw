<?php 

// attendees.php template file 

function search() {
	$res .= 'search ' . $input->get->email;
	return $res;
}

if ($input->urlSegment1 === 'add') {
	$content = attendeeForm( $page, true );

} elseif ($input->urlSegment1 === 'new') {
	$new_page = attendeeSave( $page, $input, true);
	$session->redirect($new_page->url, false);

} elseif ($input->urlSegment1 === 'search') {
	search();
}

// if the rootParent (section) page has more than 1 child, then render 
// section navigation in the sidebar
if($page->rootParent->hasChildren > 1) {
	$sidebar = renderNavTree($page->rootParent, 1) . $page->sidebar; 
}


