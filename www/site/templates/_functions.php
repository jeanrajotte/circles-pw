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

  define('DOWN_ARR', '&#8659;');
  define('RIGHT_ARR', '&#8658;');

  $event = $page->parent('template.name=event');

  // trace($is_new ? 'NEW' : 'NOT NEW');
  
  $c = $is_new ? 'adult' :  ($page->is_child ? 'child' : 'adult');
  $url = $is_new ? $page->url.'create' : $page->url.'save';

  $res .= '<div class="well required">Fields marked like this paragraph are <b>[required]</b>. You will not be able to save this form with values in them.</div>';

  $res .= '<form class="tbl '.$c.'" id="attendee" action="'.$url.'" method="post">';
  $res .= '<table class="circles">';

  ///////// EMAIL

  $val = $is_new 
    ? (wire('input')->get->email
      ? 'value="' . wire('sanitizer')->email( wire('input')->get->email) .'"'
      : '') 
    : ' value="' .$page->email. '"';
  $search_url = $event->url . 'attendees/search?email=' . urlencode($page->email);
  $add_url = $event->url . 'attendees/add?email=' . urlencode($page->email);

  $res .= '<tr class="required">';
  $res .= '<td>' . '<label for="email"><span class="fname">Your Email</span><em>to retrieve this info if you want to change it</em></label>' . '</td>';
  $res .= '<td colspan="3">' . '<p><input type="email" name="email" id="email" size="40"'.$val.' /></p>' 
    . '<p><a class="btn btn-xs btn-primary" href="'.$search_url.'" >Find all entries</a>'
    . ' <em> with this email</em></p>'
    . '<p><a class="btn btn-xs btn-primary" href="'.$add_url.'" >Add</a>'
    . ' <em>another person to this email</em></p>'
    . '</td>';
  $res .= '</tr>';

  /////// NAME

  $val = $is_new ? '' : ' value="' .$page->title. '"';

  $res .= '<tr class="required">';
  $res .= '<td>' . '<label for="title"><span class="fname">The name you are registering</span><em>yourself or child or ...</em></label>' . '</td>';
  $res .= '<td colspan="3">' . '<input type="text" name="title" id="title" size="40"'.$val.' />' . '</td>';
  $res .= '</tr>';

  $res .= '</table>';

  $res .= '<div class="well generous">';
  $res .= '<p>Click <span class="slct">' .DOWN_ARR.'</span> below to set/clear all check boxes in the column.</p>';
  $res .= '<p>Click <span class="slct">' .RIGHT_ARR.'</span> below to set/clear all check boxes in the row.</p>';
  $res .= '</div>';

  $res .= '<table class="table-striped circles">';

  //////// AMENITIES

  $amenities = wire('pages')->find('parent=/lookups/amenities, template.name=amenity, closed=0, sort=sort');

  $res .= '<tr>';
  $res .= '<td></td>';
  $res .= '<td></td>';
  foreach($amenities as $a) {
    $res .= '<th class="text-right">' . $a->title . '</th>';
  }
  $res .= '</tr>';

  /////// CHILD OR NOT, WITH PRICE ROWS

  $val_a = $is_new ? 'checked ' : ($page->is_child ? '' : 'checked ');
  $val_c = $is_new ? '' : (!$page->is_child ? '' : 'checked ');

  $res .= '<tr>';
  $res .= '<td>' . '<label for="is_child_a">Adult</label>' . '</td>';
  $res .= '<td class="text-right">' . '<input type="radio" id="is_child_a" name="is_child" value="0" '.$val_a.'/>' . '</td>';
  foreach($amenities as $a) {
    $res .= '<td class="text-right">' . '<span class="price adult" amenity="'.$a->name.'" price="'.$a->price_adult.'">' . currency( $a->price_adult, '--') .'</span>' . '</td>';
  }
  $res .= '</tr>';

  $res .= '<tr>';
  $res .= '<td>' . '<label for="is_child_c">Child</label>' . '</td>';
  $res .= '<td class="text-right">' . '<input type="radio" id="is_child_c" name="is_child" value="1" '.$val_c.'/>' . '</td>';  
  foreach($amenities as $a) {
    $res .= '<td class="text-right">' . '<span class="price child" amenity="'.$a->name.'" price="'.$a->price_child.'">' . currency( $a->price_child, '--') .'</span>' . '</td>';
  }
  $res .= '</tr>';

  //////// ALL selectors

  $res .= '<tr>';
  $res .= '<td></td>';
  $res .= '<td>' . '<label class="slct">ALL</label>' . '</td>';
  foreach($amenities as $a) {
    $res .= '<td class="text-right">' . '<a href="#" class="slct amenity" amenity="' .$a->name. '" >'.DOWN_ARR.'</a>'. '</td>';
  }
  $res .= '</tr>';

  //////// EVENT AMENITIES PER DAY

  $d0 = new DateTime();
  $d0->setTimestamp( $event->getUnformatted( "date_from"));
  $d_from = $d0->format("Y-m-d");
  $d9 = new DateTime();
  $d9->setTimestamp( $event->getUnformatted( "date_to"));
  $d_to = $d9->format("Y-m-d");

  $event_amenities = $event->child('template=amenities');

  while($d_from <= $d_to){
    $res .= '<tr>';
    $res .= '<td>' .  '<label>' . $d0->format('D, M d') . '</label>' . '</td>';
    $res .= '<td class="text-right">' . '<a href="#" class="slct date" >'.RIGHT_ARR.'</a>'. '</td>';
    foreach($amenities as $a) {
      $name = wire('sanitizer')->name($d_from . '-' . $a->name);
      $e_a = $event_amenities->child( "name=$name");
      if (!$e_a->id) {
        $res .= "<td>$name</td>";
      } else {
        $val = $is_new ? '' : ($page->attendee_amenities->get( $e_a) ? 'checked ' : '');
        $res .= '<td class="text-right">' . '<input type="checkbox" amenity="' .$a->name. '" id="'.$e_a->name.'" name="'.$e_a->name.'" '.$val.'/>' . '</td>';
      }
    }
    $res .= '</tr>';
    $d0->modify('+1 day');
    $d_from = $d0->format("Y-m-d");
  }

  $btn_label = ($is_new ? 'Register' : 'Save');

  $res .= <<<END
</table>
<div class="bg-default"></div>
<div class="bg-danger" id="err"></div>
<br/>
<div class="band">
  <h4 class="pull-left">Total: &nbsp; <span id="total">--</span></h4>
  <button id="btn-save" class="btn btn-success pull-right">{$btn_label}</button>
</div>
</form>
END;

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

$('#btn-save').click(function(ev) {
  var missing = [];
  $('form .required').each(function() {
    var str = $(this).find('input').val();
    // console.log(str);
    if (str.trim() === '') {
      missing.push( '<li>' + $(this).find('.fname').html());
    }
  });
  if (missing.length) {
    $('#err')
      .addClass('generous')
      .html( 'Missing information.  Please fill out these fields in order to register:\\n\\n' 
        + '<ul>' + missing + '</ul>' );
    ev.preventDefault();
  }
  return missing.length===0 ;
});

$('.slct.amenity').click(function(ev) {
  ev.preventDefault();
  var state = true;
  $('#attendee input[type=checkbox][amenity='+$(this).attr('amenity')+']')
  .each(function() {
    state &= this.checked;
  })
  .each(function() {
    this.checked = !state;
  });
  updateTotals();
});

$('.slct.date').click(function(ev) {
  ev.preventDefault();
  var state = true;
  $(this).parents('tr').find('input[type=checkbox]')
  .each(function() {
    state &= this.checked;
  })
  .each(function() {
    this.checked = !state;
  });
  updateTotals();
});


function updateTotals() {
  var isChild = $('#attendee').hasClass('child'), 
  c = '.price.' + (isChild ? 'child' : 'adult');
  console.log('C', c);
  // find prices once
  prices = {};
  $('#attendee ' + c).each(function() {
    prices[ $(this).attr('amenity')] = $(this).attr('price');    
  });
  console.log('PRICES', prices);
  var total = 0;
  $('#attendee input[type=checkbox]').each(function() {
    if (this.checked) {
      n = prices[ $(this).attr('amenity') ];
      console.log( this.name, n);
      total += parseFloat(n);
    }
  });
  $('#total').html('$' + total.toFixed(2));
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
  trace( 'SAVE name: ' . $p->name );
  $p->of(false);
  $p->title = $sanitizer->text( $input->post->title );
  $p->email = $sanitizer->email( $input->post->email );
  $p->is_child = $input->post->is_child ? true : false;

  foreach($p->parent('template.name=event')->find('template.name=event_amenity') as $a) {
    if ($input->post( $a->name )) {
      $p->attendee_amenities = $a;
      trace( 'add: ' . $a->name . ' : ' . $input->post( $a->name ));
    } elseif($p->attendee_amenities->get($a)) { 
      $p->attendee_amenities->remove($a);
      trace( 'remove: ' . $a->name . ' : ' . $input->post( $a->name ));
    }
  } 
  
  trace( 'title: ' . $p->title );
  trace( 'ARG!: ' . $p->attendee_amenities);
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
  echo '<pre class="trace">';
  echo "TRACE:\n";
  foreach($trace_lines as $line) {
    echo "$line\n";
  }
  echo '</pre>';
}

function _table($rows, $config) {
  extract( $config); 
  if (!$tag0) {
    $tag0 = $tag;
  }
  ob_start();
  echo "<table $tbl_atts>";
  $t = $tag0;
  foreach( $rows as $row) {
    echo "<tr $tr0_atts>";
    foreach( $row as $td) {
      extract($td);
      echo "<{$t} {$atts}>{$val}</{$t}>";
    }
    echo "</tr>";
    $t = $tag;
  }
  echo "</table>";
  return ob_get_clean();

}

function _a( $href, $txt, $classes=false) {
  $c = $classes ? 'class="'.$classes.'"' : '';
  return <<<EOT
<a href="$href" $c>$txt</a>
EOT;
}

function _td( $val, $atts='') {
  return array(
    'val' => $val,
    'atts' => $atts
  );
}

