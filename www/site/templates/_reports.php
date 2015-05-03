<?php


/////////// reports /////////////

function reports() {
  return report_charges() . report_meals();
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

  // grab all amenities upfront 
  $amenities = wire('pages')->find('template.name=amenity, sort=title');
  $gtot = array();
  $cats = array();
  foreach($amenities as $a) {
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
  foreach(wire('page')->children('sort=email, sort=title') as $p) {
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
    foreach($amenities as $a) {
      if ($p->attendee_amenities->find($a)->count()) {
        $cat = $a->category->title;
        $n = $a->get($fld);
        // attendee totals
        $tot[ $cat] = $tot[ $cat] + $n;
        $tot[ TOT] = $tot[ TOT] + $n; 
        // grand totals
        $gtot[ $cat] = $gtot[ $cat] + $n;
        $gtot[ TOT] = $gtot[ TOT] + $n; 
        // email totals
        $cur_email_tot[ $cat] = $cur_email_tot[ $cat] + $n;
        $cur_email_tot[ TOT] = $cur_email_tot[ TOT] + $n; 
      }
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

function report_meals() {

  // grab all amenities upfront 
  $amenities = wire('pages')->find('template.name=amenity, sort=title');
  $tot_a = array();
  $tot_c = array();
  $cats = array();
  foreach($amenities as $a) {
    if ($a->category->title === 'Food') {
      $cats[] = $a->title;   
    }
  }
  $cats[] = TOT;

  foreach($cats as $cat) {
    $tot_a[ $cat] = 0;
    $tot_c[ $cat] = 0;
  }

  $config = array(
    'tbl_atts' => 'class="circles table-striped"',
    'tag0' => 'th',
    'tag' => 'td',
  ); 

  $rows = array();
  $r = array();
  $r[] = _td( 'Meals');
  $r[] = _td( 'Total', 'class="text-right b"');
  $r[] = _td( 'Adults', 'class="text-right b"');
  $r[] = _td( 'Children', 'class="text-right b"');
  $rows[] = $r;
  
  foreach(wire('page')->children('sort=email') as $p) {
    foreach($amenities as $a) {
      if ($p->attendee_amenities->find($a)->count()) {
        if ($p->is_child) {
          $tot_c[$a->title] += 1;
        } else {
          $tot_a[$a->title] += 1;
        }
      }
    }
  }

  foreach($cats as $cat) {
    if ($cat!==TOT) {
      $tot_a[TOT] += $tot_a[ $cat];
      $tot_c[TOT] += $tot_c[ $cat];
    }  
  }  
  foreach($cats as $cat) {
    $r = array();
    $c = $cat === TOT ? 'class="text-right b tot"' : 'class="text-right"';
    $c_t = $cat === TOT ? 'class="text-right b tot"' : 'class="text-right b"';
    $r[] = _td( $cat);
    $r[] = _td( $tot_a[ $cat] + $tot_c[ $cat], $c_t);
    $r[] = _td( $tot_a[ $cat], $c);
    $r[] = _td( $tot_c[ $cat], $c);
    $rows[] = $r;
  } 

  return "<h2>Meal Counts</h2>" . _table( $rows, $config);
}

