<?php


/////////// reports /////////////

function reports() {
  return report_charges() . report_meals() . report_on_site();
} 


define('TOT', 'Totals');

function _group_total( $cats, $cur_email, $cur_email_tot) {
  $r = array();
  $r[] = _td( $cur_email, 'class="b"');
  $r[] = _td( '');
  foreach($cats as $cat) {
    $r[] = _td( '<span class="price">'.currency($cur_email_tot[$cat]).'</span>', 'class="text-right b"');
  }
  return $r;
}

function report_charges() {
  // establish where we at
  $event_attendees = wire('page');

  // define categories
  $gtot = array();
  $cats = array();
  foreach(wire('pages')->find('template.name=amenity, closed=0') as $a) {
    $cat = $a->category->title;
    if (!in_array($cat, $cats)) {
      $cats[] = $cat;   
    }
  }
  $cats[] = TOT;
  foreach($cats as $cat) {
    $gtot[ $cat] = 0;
  }

  $config = array(
    'tbl_atts' => 'class="circles table-striped"',
    'tag0' => 'th',
    'tag' => 'td',
  ); 
  
  $rows = array();
  $r = array();
  $r[] = _td( 'Email');
  $r[] = _td( 'Name');
  foreach($cats as $cat) {
    trace( 'cat: ' .$cat);
    $r[] = _td( $cat, 'class="text-right b"' );
  }
  $rows[] = $r;
  
  // accum per email
  $cur_email = '';
  $cur_email_n = 0;
  $cur_email_tot = array();

  $blank = '';
  $blank_row = array_fill(0, count($r), _td(''));
  foreach($event_attendees->children('sort=email, sort=title') as $p) {
    // show group total?
    if ($cur_email!=='' && $cur_email!==$p->email) {
      if ($cur_email_n>1) {
        $rows[] = _group_total( $cats, $cur_email, $cur_email_tot);
        $rows[] = $blank_row;
      } 
      $cur_email_tot = array();
      $cur_email_n = 0;
      $rows[] = $blank_row;
    }
    $cur_email = $p->email;
    $cur_email_n++;

    $r = array();
    $tot = array();
    foreach($cats as $cat) {
      $tot[$cat] = 0;
    }
    $fld = $p->is_child ? 'price_child' : 'price_adult';
    foreach($p->attendee_amenities as $a) {
      $cat = $a->amenity->category->title;
      $n = $a->amenity->get($fld);
      // attendee totals
      // trace($a->title . ': ' . $cat . ': '. $n);
      $tot[ $cat] = $tot[ $cat] + $n;
      $tot[ TOT] = $tot[ TOT] + $n; 
      // grand totals
      $gtot[ $cat] = $gtot[ $cat] + $n;
      $gtot[ TOT] = $gtot[ TOT] + $n; 
      // email totals
      $cur_email_tot[ $cat] = $cur_email_tot[ $cat] + $n;
      $cur_email_tot[ TOT] = $cur_email_tot[ TOT] + $n; 
    }
    $t = 0;

    $r[] = _td( $p->email);
    $r[] = _td( _a( $p->url, $p->title));

    // use gtot to catch all cats 
    foreach($cats as $cat) {
      $r[] = _td( '<span class="price">'.currency($tot[ $cat]).'</span>', 'class="text-right"');
    }
    $rows[] = $r;  
  }
  if ($cur_email_n>1) {
    $rows[] = _group_total( $cats, $cur_email, $cur_email_tot);
    $cur_email_tot = array();
    $cur_email_n = 0;
  }
  
  $r = array();
  $r[] = _td( 'Totals', 'colspan="2" class="b"');
  foreach($cats as $cat) {
    $r[] = _td( '<span class="price">'. currency($gtot[$cat]).'</span>', 'class="text-right b tot"');
  }
  $rows[] = $r;

  return "<h2>Charges</h2>" . _table( $rows, $config);

}

function _report_head_count( $cat ) {

  // establish where we at.
  $event_attendees = wire('page');
  $event = $event_attendees->parent;

  // grab all event amenities upfront 
  $event_amenities = $event->find('template.name=event_amenity, sort=sort');
  $tots_a = array();
  $tots_c = array();
  $cols = array();
  foreach($event_amenities as $e_a) {
    if ($e_a->amenity->category->title === $cat) {
      $ts = $e_a->getUnformatted( "date");
      // trace($ts);
      $col = $e_a->amenity->title;
      if (!array_key_exists($ts, $tot_a)) {
        $tot_a[$ts] = array();
      }
      $tot_a[$ts][$col] = 0;
      $tot_c[$ts][$col] = 0;
      $cols[$col] = true;
    }
  }
  
  // trace( print_r($tot_a, true));

  $config = array(
    'tbl_atts' => 'class="circles table-striped"',
    'tag0' => 'th',
    'tag' => 'td',
  ); 

  $rows = array();
  $r = array();
  $r[] = _td( $cat, 'style="border-right:1px silver solid"');
  foreach($cols as $name => $dummy) {
    $r[] = _td( $name, 'colspan="2" class="text-center b"  style="border-right:1px silver solid"');
  }
  $rows[] = $r;

  $r = array();
  $r[] = _td( '', 'style="border-right:1px silver solid"');
  foreach($cols as $name => $dummy) {
    $r[] = _td( 'Adults', 'class="text-right"');
    $r[] = _td( 'Children', 'class="text-right" style="border-right:1px silver solid"');
  }
  $rows[] = $r;
  
  // trace( $event_attendees->children()->count() );
  foreach($event_attendees->children() as $p) {
    // trace($p->email);
    foreach($p->attendee_amenities as $e_a) {
      // trace('   ' . $e_a->title);
      if ($e_a->amenity->category->title === $cat) {
        $ts = $e_a->getUnformatted( "date");
        $col = $e_a->amenity->title;
        if ($p->is_child) {
          $tot_c[$ts][$col] += 1;
        } else {
          $tot_a[$ts][$col] += 1;
        }
      }
    }
  }

  // trace( print_r($tot_a, true));

  $d = new Datetime();
  foreach($tot_a as $ts => $dummy) {
    $r = array();
    $d->setTimestamp( $ts);
    $r[] = _td( $d->format('D, M d'), 'class="b" style="border-right:1px silver solid"');
    foreach($cols as $col => $dummy) {
      $r[] = _td( $tot_a[$ts][$col], 'class="text-right"');
      $r[] = _td( $tot_c[$ts][$col], 'class="text-right" style="border-right:1px silver solid"');
    }
    $rows[] = $r;
  } 
  return _table( $rows, $config);
}

function report_meals() {
  return '<h2>Meal Counts</h2>' . _report_head_count( 'Food') ;
}


function report_on_site() {
  return '<h2>On-Site Counts</h2>' . _report_head_count( 'Lodging') ;
}


