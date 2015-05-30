<?php

// attendee template

if ($input->post->delete) {
	$page->delete();
	$page->save();
	$session->redirect($page->parent('template.name=event')->url, false);
}

if ($input->urlSegment1 === 'save') {
	trace('attendee page');
	attendeeSave( $page, $input);
	// redirect in order to lose the "save" segment
	$session->redirect($page->url, false);
}
$content = <<<EOT
<div class="generous bg-success">
	<h4>Registered</h4>
	<p>
    Change the info and press Save below,
  </p>
	<form action="{$page->url}delete" method="post">
    <input type="hidden" name="delete" value="1" />
		or <button id="btn-del" class="btn btn-xs btn-danger">Unregister
		</button> from this event.
	</form>
</div>
<script>
$('#btn-del').click(function(ev) {
  if (!confirm('Are you sure?')) {
    ev.preventDefault();
    return false;
  }
});
</script>
EOT;

$content .= attendeeForm( $page );

// if the rootParent (section) page has more than 1 child, then render 
// section navigation in the sidebar
if($page->rootParent->hasChildren > 1) {
	$sidebar = renderNavTree($page->rootParent, 1) . $page->sidebar; 
}
