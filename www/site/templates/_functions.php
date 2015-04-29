<?php

// functions

function currency($x, $zero='$0.00') {
	if ($x==0) {
		return $zero;
	}
	return '$' . sprintf( '%01.2f', $x);
}

function attendeeForm( $page, $is_new=false ) {
	$res = '';
	$val = '';
	$val_a = '';
	$val_b = '';

	if (!$is_new) {
		$page->of(true);
	}

	// trace($is_new ? 'NEW' : 'NOT NEW');
	
	$c = $is_new ? 'adult' :  ($page->is_child ? 'child' : 'adult');
	$url = $is_new ? $page->url.'create' : $page->url.'save';

	$res .= '<form class="tbl '.$c.'" id="attendee" action="'.$url.'" method="post">';
	$res .= '<table class="table-striped">';

	$val = $is_new 
		? (wire('input')->get->email
			? 'value="' . wire('sanitizer')->email( wire('input')->get->email) .'"'
			: '') 
		: ' value="' .$page->email. '"';
	$search_url = $page->parent('template.name=event')->url . 'attendees/search?email=' . urlencode($page->email);
	$add_url = $page->parent('template.name=event')->url . 'attendees/add?email=' . urlencode($page->email);

	$res .= '<tr>';
	$res .= '<td>' . '<label for="email">Your Email<em>to retrieve this info if you want to change it</em></label>' . '</td>';
	$res .= '<td colspan="3">' . '<p><input type="email" name="email" id="email" size="40"'.$val.' /></p>' 
		. '<p><a class="btn btn-xs btn-primary" href="'.$search_url.'" >Find all entries</a>'
		. ' <em> with this email</em></p>'
		. '<p><a class="btn btn-xs btn-primary" href="'.$add_url.'" >Add</a>'
		. ' <em>another person to this email</em></p>'
		. '</td>';
	$res .= '</tr>';


	$val = $is_new ? '' : ' value="' .$page->title. '"';

	$res .= '<tr>';
	$res .= '<td>' . '<label for="title">The name you are registering<em>yourself or child or ...</em></label>' . '</td>';
	$res .= '<td colspan="3">' . '<input type="text" name="title" id="title" size="40"'.$val.' />' . '</td>';
	$res .= '</tr>';

	$val_a = $is_new ? 'checked ' : ($page->is_child ? '' : 'checked ');
	$val_c = $is_new ? '' : (!$page->is_child ? '' : 'checked ');

	$res .= '<tr>';
	$res .= '<td colspan="2">' . '<label>Is s/he a child?</label>' . '</td>';
	$res .= '<td class="text-right">' . '<label><input type="radio" name="is_child" value="0" '.$val_a.'/> No</label>' . '</td>';
	$res .= '<td class="text-right">' . '<label><input type="radio" name="is_child" value="1" '.$val_c.'/> Yes</label>' . '</td>';
	$res .= '</tr>';

	foreach(wire('pages')->find('template.name=amenity, sort=sort') as $a) {

		$val = $is_new ? '' : ($page->attendee_amenities->get( $a) ? 'checked ' : '');
		
		$res .= '<tr>';
		$res .= '<td>' . '<label for="' . $a->name . '">' . $a->title . '</label>' . '</td>';
		$res .= '<td>' . '<input type="checkbox" id="'.$a->name.'" name="'.$a->name.'" '.$val.'/>' . '</td>';
		$res .= '<td class="text-right">' . '<span class="price adult" price="'.$a->price_adult.'">' . currency( $a->price_adult, '--') .'</span>' . '</td>';
		$res .= '<td class="text-right">' . '<span class="price child" price="'.$a->price_child.'">' . currency( $a->price_child, '--') .'</span>' . '</td>';
		$res .= '</tr>';
	}

	$res .= '<tr>';
	$res .= '<td colspan="2">' . '<label>Total</label>' . '</td>';
	$res .= '<td class="text-right">' . '<span class="price adult total">--</span>' . '</td>';
	$res .= '<td class="text-right">' . '<span class="price child total">--</span>' . '</td>';
	$res .= '</tr>';

	$res .= '</table>';
	$res .= '<div><button class="btn btn-success">'.($is_new ? 'Register' : 'Save').'</div>';
	$res .= '</form>';


	$res .= <<<END
<script>
$('#attendee')
	.on('click', '[name=is_child]', function(ev) {
		console.log($(ev.target).val());
		if ($(ev.target).val()==1) {
			$('#attendee').removeClass('adult').addClass('child');
		} else {
			$('#attendee').removeClass('child').addClass('adult');
		}
		updateTotals();
	})
	.on('change', '[type=checkbox]', function(ev) {
		updateTotals();
	});

function updateTotals() {
	var isChild = $('#attendee').hasClass('child'), 
	c = '.price.' + (isChild ? 'child' : 'adult');
	var total = 0;
	console.log('C', c);
	$('#attendee input[type=checkbox]').each(function() {
		if (this.checked) {
			n = $(this).parents('tr').find( c).attr('price');	
			console.log( this.name, n);
			total += parseFloat(n);
		}
	});
	$('#attendee .price.adult.total').html(isChild ? '--' : '$' + total.toFixed(2));
	$('#attendee .price.child.total').html(isChild ? '$' + total.toFixed(2) : '--');
}

updateTotals();

</script>
END;

	return $res;
} 

function attendeeSave($page, $input, $is_new) {
	$p = $page;
	$sanitizer = wire('sanitizer');
	if ($is_new) {
		$p = new Page();
		$p->template = 'attendee';
		$p->parent = $page;		
	}
	$p->of(false);
	$p->title = $sanitizer->text( $input->post->title );
	$p->email = $sanitizer->email( $input->post->email );
	$p->is_child = $input->post->is_child ? true : false;

	foreach(wire('pages')->find('template.name=amenity, sort=sort') as $a) {
		// trace( $a->name . ' : ' . $input->post( $a->name ));
		if ($input->post( $a->name )) {
			$p->attendee_amenities = $a;
		} elseif($p->attendee_amenities->get($a)) { 
			$p->attendee_amenities->remove($a);
		}
	}	
	
	// trace( 'name: ' . $p->name );
	// trace( 'title: ' . $p->title );

	$p->save();
	return $p;
}

function renderNextEvent() {
	$d = new Datetime();
	$d->modify('- 3 days');
	$s = $d->format('Y-m-d');
	trace($s);
	$p = wire('pages')->findOne("template.name=event,date_from>$s,sort=date_from");
	if ($p) {
		return '<a class="btn btn-primary" href="'.$p->url.'">' .$p->title. '</a>';
	} else {
		return '<p class="generous bg-warning">No Next Event right now...</p>';
	}
}

$trace_lines = array();
function trace($s) {
	global $trace_lines;
	$trace_lines[] = $s;
}
function trace_show() {
	global $trace_lines;
	if (count($trace_lines)) {
		echo '<pre class="trace">';
		foreach($trace_lines as $line) {
			echo "$line\n";
		}
		echo '</pre>';
	}
}
