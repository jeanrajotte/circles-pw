<?php 

// attendees.php template file 

function search( ) {
	$email = wire('sanitizer')->text(wire('input')->get->email); 
	if (!$email) {
		return '<p class="well text-danger">' 
		. 'Nothing ventured, nothing gained. Please provide an email address for the search'
		. '</p>';
	}
	$found = wire('page')->find( 'template.name=attendee,email%=' .$email. ',sort=email');
	if ($found->count()) {
		$out = '';
		foreach($found as $item) {
			$out .= "<tr>";
			$out .= "<td><a href='$item->url'>$item->title</a></td>";
			$out .= "<td><i>($item->email)</i></td>";
			$out .= "</tr>";
		}
		return "<table class=\"circles\">$out</table>";
	} else {
		return '<p class="well text-danger">Nothing found for ' .$email. '</p>';
	}
}

if ($input->urlSegment1 === 'add') {
	$content = attendeeForm( $page, true );
	$title = $page->title = 'Add New Attendee';

} elseif ($input->urlSegment1 === 'create') {
	$new_page = attendeeSave( $page, $input, true);
	$session->redirect($new_page->url, false);

} elseif ($input->urlSegment1 === 'search') {
	$content = search();
	$title = $page->title = 'Search Results';
} elseif ($input->urlSegment1 === 'reports') {
	$content = reports();
	$title = $page->title = $page->parent->title . ' Info';
}

// if the rootParent (section) page has more than 1 child, then render 
// section navigation in the sidebar
if($page->rootParent->hasChildren > 1) {
	$sidebar = renderNavTree($page->rootParent, 1) . $page->sidebar; 
}


