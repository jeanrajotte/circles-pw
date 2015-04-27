<?php 

// attendees.php template file 

function search( ) {
	$email = wire('sanitizer')->email(wire('input')->get->email); 
	if (!$email) {
		return '<p class="well text-danger">' 
		. 'Nothing ventured, nothing gained. Please provide an email address for the search'
		. '</p>';
	}
	$found = wire('page')->find( 'template.name=attendee,email=' .$email);
	if ($found->count()) {
		return renderNav($found);
	} else {
		return '<p class="well text-danger">Nothing found for ' .$email. '</p>';
	}
}

function report() {
	$amenities = wire('pages')->find('template.name=amenity');
	$res = '<table class="circles table-striped">';

	$gtot = 0;
	foreach(wire('page')->children('sort=email') as $p) {
		$tot = 0;
		$fld = $p->is_child ? 'price_child' : 'price_adult';
		foreach($amenities as $a) {
			if ($p->attendee_amenities->find($a)) {
				$tot += $a->get($fld);
			}
		}
		$gtot += $tot;		

		$res .= '<tr>';
		$res .= '<td>'.$p->email.'</td>';
		$res .= '<td>'.'<a href="'.$p->url.'">'.$p->title.'</a>'.'</td>';
		$res .= '<td class="text-right"><span class="price">'. currency($tot) .'</span>'.'</td>';
		$res .= '</tr>';
	}
	$res .= '<tr>';
	$res .= '<td colspan="2">Total</td>';
	$res .= '<td class="text-right"><span class="price">'. currency($gtot) .'</span>'.'</td>';
	$res .= '</tr>';

	$res .= '</table>';
	return $res; 

}

if ($input->urlSegment1 === 'add') {
	$content = attendeeForm( $page, true );
	$title = $page->title = 'Add New Attendee';

} elseif ($input->urlSegment1 === 'new') {
	$new_page = attendeeSave( $page, $input, true);
	$session->redirect($new_page->url, false);

} elseif ($input->urlSegment1 === 'search') {
	$content = search();
	$title = $page->title = 'Search Results';
} elseif ($input->urlSegment1 === 'report') {
	$content = report();
	$title = $page->title = 'Attendees Report';
}

// if the rootParent (section) page has more than 1 child, then render 
// section navigation in the sidebar
if($page->rootParent->hasChildren > 1) {
	$sidebar = renderNavTree($page->rootParent, 1) . $page->sidebar; 
}


