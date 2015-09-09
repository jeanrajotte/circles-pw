<?php

// basic-page.php template file
// See README.txt for more information

// Primary content is the page's body copy
$content = $page->body;

$ls = array();
foreach($page->attachments as $f) {
	$ls[] = "<a href=\"{$f->url}\">{$f->description}</a>";
}
if (count($ls)) {
	$content .= "<h4>Attachments</h4><div>".implode('<br/>',$ls)."</div>";
}

if ($page->closed) {
	$content .= "<div class=\"well\">What's done is done! Nothing to change here.</div>";
} else {
	// $add_url = $page->url. 'attendees/add';
	$content .= <<<END
<h3>Not already registered</h3>
<div class="generous">
	<a class="btn btn-primary" href="attendees/add">Add yourself or someone else to attend this event</a>
</div>
<h3>Already registered</h3>
<div class="generous">
	<form action="attendees/search">
		<label>Enter some text found in the email address you used when you registered  <input type="text" name="email"></label>
		<button class="btn btn-warning">Search</button>
	</form>
</div>
END;

}

if (!$page->closed) {
	$content .= <<<END
<div class="generous">
	<a class="btn btn-primary" href="attendees/reports">Attendees reports</a>
</div>
END;
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
