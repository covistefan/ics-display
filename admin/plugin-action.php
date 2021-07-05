<?php
// Exit if accessed directly
if (!defined('ABSPATH')) { exit; }

$plugin_name = ICSD_TEXT_DOMAIN;
$plugin_url = ICSD_PLUGIN_URL;

// activate PRO version
if (isset($_POST['icsd_premium'])) {
    $res = set_ics_premium(sanitize_text_field(trim($_POST['icsd_premium'])));
    if ($res===true) {
        echo '<div class="updated notice inline is-dismissable"><p>'.__( 'Thank you for activating premium features', ICSD_TEXT_DOMAIN ).'</p></div>';
    } else {
        echo '<div class="error notice inline is-dismissable"><p>'.__( $res['msg'], ICSD_TEXT_DOMAIN ).'</p></div>';
    }
}
$icsd_url = maybe_unserialize(get_option('icsd_url'));
// remove submitted url from list if removed
if (isset($_POST['icsd_url_remove']) && intval($_POST['icsd_url_remove'])>0) {
    // get the key associated to submitted url
    $icsd_url[(intval($_POST['icsd_url_remove'])-1)] = false;
    update_option('icsd_url', serialize($icsd_url));
}
// add a new url to set
if (isset($_POST['icsd_url_new']) && trim($_POST['icsd_url_new'])!='') {
    if (is_array($icsd_url)) {
        $icsd_url[] = esc_url_raw($_POST['icsd_url_new']);
    } else {
        $icsd_url = array(esc_url_raw($_POST['icsd_url_new']));
    }
    update_option('icsd_url', serialize($icsd_url));
}
// prepare $icsd_url to display only existing urls
if (is_array($icsd_url)) {
    foreach ($icsd_url AS $ik => $iv) {
        if ($iv===false) { unset($icsd_url[$ik]); }
    }
    if (is_array($icsd_url) && count($icsd_url)==0) { $icsd_url = false; }
}  
// save setup
if (isset($_POST['icsd_setup'])) {
    $store = maybe_unserialize(get_option('icsd_setup'));
    foreach ($_POST['icsd_setup'] AS $psk => $psv) {
        // option to change URL 
        
        // storing setup data
        $store[$psk]['store'] = time();
        $store[$psk]['name'] = sanitize_text_field(trim($psv['name']));
        $store[$psk]['display'] = sanitize_text_field(trim($psv['display']));
        $store[$psk]['sort'] = sanitize_text_field(trim($psv['sort']));
        $store[$psk]['startdate'] = sanitize_text_field(trim($psv['startdate'])); // added in 2.0
        $store[$psk]['sort'] = sanitize_text_field(trim($psv['sort']));
        $store[$psk]['max'] = intval($psv['max']); // added in 2.0
        $store[$psk]['repeats'] = intval($psv['repeats']); // added in 2.0
        $store[$psk]['page'] = intval($psv['page']); if ($store[$psk]['display']=='calendar' && $store[$psk]['page']>12) { $store[$psk]['page'] = 12; } 
        $store[$psk]['dataset'] = array();
        foreach ($psv['dataset'] AS $pdk => $pdv) {
            if ($pdv==1) {
                $store[$psk]['dataset'][$pdk] = (trim($psv['header'][$pdk])!='')?trim($psv['header'][$pdk]):__($pdk, ICSD_TEXT_DOMAIN);
            }
        }
        
    }
    update_option('icsd_setup', serialize($store));
}
$icsd_setup = maybe_unserialize(get_option('icsd_setup'));
$icsd_setup_default = array(
    'name' => '',
    'display' => 'table',
    'sort' => 'asc',
    'startdate' => '',
    'max' => 0,
    'repeats' => 3,
    'page' => 10,
    'dataset' => array(
        'date' => __('date', ICSD_TEXT_DOMAIN),
        'time' => __('time', ICSD_TEXT_DOMAIN),
        'event' => __('event', ICSD_TEXT_DOMAIN),
        'location' => __('location', ICSD_TEXT_DOMAIN)
    )
);
$icsd_dataset_default = array(
    'date' => __('date', ICSD_TEXT_DOMAIN),
    'time' => __('time', ICSD_TEXT_DOMAIN),
    'event' => __('event', ICSD_TEXT_DOMAIN),
    'location' => __('location', ICSD_TEXT_DOMAIN),
    'dtbegin' => __('dtbegin', ICSD_TEXT_DOMAIN),
    'dtend' => __('dtend', ICSD_TEXT_DOMAIN),
    'dtstamp' => __('dtstamp', ICSD_TEXT_DOMAIN),
    'description' => __('description', ICSD_TEXT_DOMAIN),
    'summary' => __('summary', ICSD_TEXT_DOMAIN)
);
$icsd_display_icon = array(
    'table' => '<span class="dashicons dashicons-editor-table"></span>',
    'list' => '<span class="dashicons dashicons-list-view"></span>',
    'calendar' => '<span class="dashicons dashicons-calendar-alt"></span>'
);
if (is_array($icsd_setup)) {
    foreach ($icsd_setup AS $sk => $sv) {
        if (!(isset($icsd_url[$sk]))) {
            unset($icsd_setup[$sk]);
        }
        else {
            if (!(isset($sv['checked'])) || isset($sv['checked']) && intval($sv['checked'])<(time()-86400*7)) {
                if (($this->icsd_check_data($icsd_url[$sk]))>0) {
                    $icsd_setup[$sk]['checked'] = time();
                } else {
                    echo '<div class="error notice inline is-dismissable"><p>';
                    printf(__('Error reading events from "%s"', ICSD_TEXT_DOMAIN ),((isset($sv['name']) && trim($sv['name'])!='')?trim($sv['name']):$icsd_url[$sk]));
                    echo '</p></div>';
                }
            }
        }
    }
    update_option('icsd_setup', serialize($icsd_setup));
}