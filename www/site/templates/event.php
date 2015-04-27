<?php 

// basic-page.php template file 
// See README.txt for more information

// Primary content is the page's body copy
$content = $page->body; 

if ($page->closed) {
	$content .= "<div class=\"well\">What's done is done! Nothing to change here.</div>";
} else {
	// $add_url = $page->url. 'attendees/add';
	$content .= "<h3>Not already registered</h3>";
	$content .= "<div>";
	$content .= "<a class=\"btn btn-primary\" href=\"attendees/add\">Add yourself or someone else to attend this event</a>";
	$content .= "</div>";
	$content .= "<h3>Already registered</h3>";
	$content .= "<div>";
	$content .= '<form action="attendees/search">';
	$content .= '<label>Enter the email address you used when you registered  <input type="email" name="email"></label> ';
	$content .= '<button class="btn btn-warning">Search</button> ';
	$content .= "</form>";
	$content .= "</div>";
}

if ($user->isLoggedin()) {
	$content .= '<div>';
	$content .= '<a class="btn btn-primary" href="attendees/report">Attendees report</a>';
	$content .= '</div>';
}

// If the page has children, then render navigation to them under the body.
// See the _func.php for the renderNav example function.
// if($page->hasChildren) {
// 	$content .= renderNav($page->children);
// }

// if the rootParent (section) page has more than 1 child, then render 
// section navigation in the sidebar
if($page->rootParent->hasChildren > 1) {
	$sidebar = renderNavTree($page->rootParent, 1) . $page->sidebar; 
}


