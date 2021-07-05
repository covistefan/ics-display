<?php
/**
 * The public plugin class.
 *
 * @since      1.0
 * @package    ICS_Display
 */

defined( 'ABSPATH' ) || exit;

class ICS_Display_Public {
    
    private $plugin_name ;
    private $icsd_timezone; // @since 2.0

    // init class
    // @since 1.0
    public function __construct( $plugin_name ) {
        $this->plugin_name = $plugin_name;
        $this->icsd_timezone = get_option('timezone_string');
    }
    
    /* public area css */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __DIR__ ) . 'css/icsd-public.css'
        );
    }
    
    /* public area javascript */
    public function enqueue_scripts() {}
    
    /* public area shortcodes */
    public function register_shortcodes() {
        add_shortcode( 'icsd', array( $this , 'ics_short' ) );
    }

    // the shortcode call
    // @since 2.0
    public function ics_short ( $atts ) {
        
        /* get the urls to grep data */
        $calurl = maybe_unserialize( get_option('icsd_url') );
        $baseid = false;
        if ($calurl!==false) {
            foreach ($calurl AS $ck => $cv) {
                if ($baseid===false && $cv!==false) {
                    $baseid = intval($ck);
                }
            }
        }
        
        $atts = shortcode_atts([
            'id' => ($baseid+1),
            'max' => false,
            'repeats' => false,
            'page' => false,
            'sort' => false,
            'startdate' => false,
            'display' => false,
            'dataset' => false,
            'header' => false
        ], $atts);
        
        $defaults = array(
            'max' => 0,
            'repeats' => 3,
            'page' => 10, // list and table view page is items per page | calendar is months per view
            'sort' => 'asc',
            'startdate' => time(),
            'display' => 'table',
            'dataset' => 'date,time,event,location',
            'header' => __('date', $this->plugin_name).','.__('time', $this->plugin_name).','.__('event', $this->plugin_name).','.__('location', $this->plugin_name)
        );
        
        // get all different ID's to get URL from
        $getid = explode(',', $atts['id']);
        $geturl = array();
        if (is_array($getid) && count($getid)>0) {
            foreach ($getid AS $gk => $gv) {
                if (isset($calurl[($gv-1)]) && trim($calurl[($gv-1)])!='') {
                    $geturl[] = $calurl[($gv-1)];
                }
            }
            if (count($getid)==1) {
                // if there is only a call to ONE shortcode
                $calatts = maybe_unserialize( get_option('icsd_setup') );
                if (isset($calatts[(intval($getid[0])-1)])) {
                    // explode the array holding the config data
                    foreach ($calatts[(intval($getid[0])-1)]['dataset'] AS $cdk => $cdv) {
                        $tmpdataset[] = $cdk;
                        $tmpheader[] = $cdv;
                    }
                    $calatts[(intval($getid[0])-1)]['id'] = intval($getid[0]);
                    $calatts[(intval($getid[0])-1)]['dataset'] = implode(',', $tmpdataset);
                    $calatts[(intval($getid[0])-1)]['header'] = implode(',', $tmpheader);
                    // replace config attributes with shortcode attributes
                    foreach ($calatts[(intval($getid[0])-1)] AS $cak => $cav) {
                        $calatts[(intval($getid[0])-1)][$cak] = (isset($atts[$cak]) && $atts[$cak]!==false)?$atts[$cak]:$cav;
                    }
                    $atts = $calatts[(intval($getid[0])-1)];
                }
            }
        }
        // run atts against defaults to insert missing values
        foreach ($defaults AS $dk => $dv) {
            if (!isset($atts[$dk])) {
                $atts[$dk] = $dv;
            }
        }

        $atts['icsd'] = $atts['id'];

        return $this->ics_display ( $atts ) ;
        
    }

    // the block call
    // @since 2.0
    public function icsd_create_block() {
        
        // lets get some facts from icsd-data
        $store = maybe_unserialize(get_option('icsd_setup'));
        if (is_array($store)) {
            $dv = 0; $opts = array();
            foreach ($store AS $sk => $sv) {
                if ($dv==0) { $dv = $sk; }
                $opts[$sk] = ((trim($sv['name'])!='')?trim($sv['name']):'ID '.$sk);
            }
        } 

        if ( ! function_exists( 'register_block_type' ) ) {
            // Gutenberg is not active.
            return;
        }
        
        wp_register_script(
            'ics-display-icsd-block',
            plugins_url( 'js/icsd-block.js', __DIR__ ), 
            array(
                'wp-blocks',
                'wp-element',
                'wp-i18n',
                'wp-components',
                'wp-editor'
            ),
            time()
        );
 
        register_block_type( 'ics-display/icsd-block', array(
            'editor_script' => 'ics-display-icsd-block',
            'render_callback' => [ $this, 'ics_show_block' ],
            'attributes' => [
                'icsdblock' => [
                    'type' => 'number',
                    'default' => $dv
                ]
            ]
        ) );
        
    }

    // the block view
    // @since 2.0
    public function ics_show_block ( $attributes = false ) {
        if (intval($attributes['icsdblock'])>0) {
            // call the display class and return what will be displayed
            return ( $this->ics_display ( array( 'icsd' => (intval($attributes['icsdblock'])+1) ) ) );
        } else {
            return false;
        }
    }

    // getting a normalized time stamp
    // @since 2.0
    private function get_ics_timestamp ( $datevalue ) {
        
        $tmpval = explode(":", $datevalue);
        $timeval = array_pop($tmpval);
        $timarg = array_shift($tmpval);
        if (substr($timarg,0,4)=='TZID') {
            // a timezone argument is given
            $timearg_arr = explode('=', $timarg);
            $timeval.= $timearg_arr[1];
        }
        else if (substr($timarg,0,5)=='VALUE') {
            // a value argument is given
            $timearg_arr = explode('=', $timarg);
            if ($timearg_arr[1]=='DATE') {
                // $timeval is only a date
                $timeval.= "T120000".date_default_timezone_get();
            }
        }
        // create timeval object
        // $timeval = date_create_immutable_from_format ( 'Ymd\THisP' , $timeval );
        $timeval = intval(strtotime($timeval));
        return $timeval;
        
    }

    // read the ics data
    // @since 1.0
    private function get_ics_data ( $url , $cache = false ) {
        
        $args['timeout'] = 30;
        
        $response = wp_remote_get( $url , $args );
        $http_code = wp_remote_retrieve_response_code( $response );
        
        if ($http_code==200) {
            $content = wp_remote_retrieve_body( $response );
            $icsdata = explode(PHP_EOL, $content);
            $act = 0;

            // echo '<pre>';
            // var_export($icsdata);
            // echo '</pre>';

            if (is_array($icsdata)) {
                foreach($icsdata AS $ik => $iv) {
                    if ($ik>0 && intval(strpos($iv, ":"))==0) {
                        if (isset($icsdata[$act])) {
                            $icsdata[$act] = trim($icsdata[$act]).trim($iv);
                        }
                        unset($icsdata[$ik]);
                    }
                    $act = $ik;
                }
                $events = array(); $e = 0;
                // setup the timezone all time values refer to
                foreach ($icsdata AS $ik => $iv) {
                    $timezone = $this->icsd_timezone;
                    if (strstr(trim($iv), 'TZID:')) { 
                        list(,$timezone) = explode(":",trim($iv)); 
                        if (trim($timezone)!='') {
                            $this->icsd_timezone = $timezone;
                        }
                    }
                }
                // run the 
                foreach ($icsdata AS $ik => $iv) {
                    if (strstr(trim($iv), 'BEGIN:VEVENT')) {
                        $events[$e] = array();
                        date_default_timezone_set($this->icsd_timezone);
                    }
                    if (strstr(trim($iv), 'DTSTART:')) { 
                        // if dtstart empty or not given, it seems to be a full day entry
                        list(,$events[$e]['dtbegin']) = explode(":",trim($iv));
                        // convert format to "normal" timestamp
                        $events[$e]['dtbegin value'] = $events[$e]['dtbegin'];
                        $events[$e]['dtbegin'] = $this->get_ics_timestamp($events[$e]['dtbegin']);
                    } else if (strstr(trim($iv), 'DTSTART;')) {
                        list(,$events[$e]['dtbegin']) = explode(";",trim($iv));
                        $events[$e]['dtbegin value'] = $events[$e]['dtbegin'];
                        $events[$e]['dtbegin'] = $this->get_ics_timestamp($events[$e]['dtbegin']);
                    }
                    if (strstr(trim($iv), 'DTEND:')) {
                        list(,$events[$e]['dtend']) = explode(":",trim($iv));
                        // convert format to "normal" timestamp
                        $events[$e]['dtend value'] = $events[$e]['dtend'];
                        $events[$e]['dtend'] = $this->get_ics_timestamp($events[$e]['dtend']);
                    } else if (strstr(trim($iv), 'DTEND;')) {
                        list(,$events[$e]['dtend']) = explode(";",trim($iv));
                        // convert format to "normal" timestamp
                        $events[$e]['dtend value'] = $events[$e]['dtend'];
                        $events[$e]['dtend'] = $this->get_ics_timestamp($events[$e]['dtend']);
                    }
                    // CREATED
                    if (strstr(trim($iv), 'RRULE:')) {
                        list(,$events[$e]['rule']) = explode(":",trim($iv));
                    }
                    if (strstr(trim($iv), 'DESCRIPTION:')) {
                        list(,$events[$e]['description']) = explode(":",trim($iv));
                    }
                    if (strstr(trim($iv), 'LOCATION:')) { 
                        list(,$events[$e]['location']) = explode(":",trim($iv));
                        $events[$e]['location'] = str_replace('  ', ' ', str_replace('  ', ' ', str_replace("\,", ", ", $events[$e]['location'])));
                    }
                    // SEQUENCE
                    if (strstr(trim($iv), 'SUMMARY:')) {
                        $sumtmp = explode(":",trim($iv));
                        // remove tag
                        array_shift($sumtmp);
                        $events[$e]['summary'] = implode(":",$sumtmp); 
                    }
                    // find final col and start over to new event element
                    if (strstr(trim($iv), 'END:VEVENT')) {
                        ksort($events[$e]);
                        $e++;
                    }
                }
            }
            return ($events);
        }
        else {
            return false;
        }
        
    }
    
    // the event list
    // @since 2.0
    // input:
    // eventlist = array of events of all grepped ics-datasets
    // fdts = first day to show
    // repeats = number of displaying repeating events (from atts)
    // max = number of max events (for calculating max repeatings)
    // output:
    // complete eventlist
    private function get_ics_events ( $eventlist , $fdts , $repeats , $max ) {
        
        // get defined walks for repeating events
        $walk = (intval($repeats)>0)?intval($repeats):intval($max);
        
        // run eventlist
        foreach ($eventlist AS $ek => $ev) {

            ksort($ev);
            if (!(isset($ev['description']))) { $eventlist[$ek]['description'] = false; } else { $eventlist[$ek]['description'] = strip_tags($ev['description']);  }
            // dtbegin
            // calculate the dates
            $eventlist[$ek]['dtbegin Y-m-d'] = date('Y-m-d', $eventlist[$ek]['dtbegin']);
            $eventlist[$ek]['dtbegin H:i:s'] = date('H:i:s', $eventlist[$ek]['dtbegin']);
            if (!(isset($ev['dtend']))) { $eventlist[$ek]['dtend'] = false; } else {
                $eventlist[$ek]['dtend Y-m-d'] = date('Y-m-d', $eventlist[$ek]['dtend']);
                $eventlist[$ek]['dtend H:i:s'] = date('H:i:s', $eventlist[$ek]['dtend']);
            }
            // dtstamp = timegap
            $eventlist[$ek]['dtstamp'] = $eventlist[$ek]['dtend'] - $eventlist[$ek]['dtbegin'];
            if (!(isset($ev['rule']))) { 
                $eventlist[$ek]['repeats'] = false;
                $eventlist[$ek]['until'] = false;
                $eventlist[$ek]['untildate'] = false;
                $eventlist[$ek]['freq'] = false;
                $eventlist[$ek]['steps'] = array(0);
            } else {
                $ruleopt = explode(";", $eventlist[$ek]['rule']);
                $rule = array('freq' => false, 'until' => false);
                foreach ($ruleopt AS $rv) {
                    $tmpopt = explode("=", $rv);
                    $rule[strtolower($tmpopt[0])] = trim($tmpopt[1]);
                }
                // if a final date is set
                if ($rule['until']!==false) {
                    $rule['until'] = strtotime($rule['until']);
                    $rule['untildate'] = date('Y-m-d', $rule['until']);
                }
                // otherwise we set the final date to one year + 1 month until now 
                else {
                    $rule['until'] = time()+(399*86400);
                    $rule['untildate'] = date('Y-m-d', $rule['until']);
                }
                if (isset($rule['byday'])) {
                    $rule['byday'] = explode(",", $rule['byday']);
                }
                // walk the frequency and fill timesteps array
                $timesteps[] = 0;
                if ($rule['freq']=='DAILY' && defined('ICSD_PRO') && ICSD_PRO) {
                    // set dtbegin to fdts with correct time
                    if ($eventlist[$ek]['dtbegin']<$fdts) {
                        $eventlist[$ek]['dtbegin'] = $eventlist[$ek]['dtbegin']+(86400*(intval(ceil(($fdts-$eventlist[$ek]['dtbegin'])/86400))));
                    }
                    // set dtend to correct timegap
                    $eventlist[$ek]['dtend'] = $eventlist[$ek]['dtbegin'] + $eventlist[$ek]['dtstamp'];
                    for ($w=0; $w<$walk; $w++) {
                        if (($eventlist[$ek]['dtbegin']+$w*86400)<$rule['until']) {
                            $timesteps[] = $w*86400;
                        }
                    }
                    $eventlist[$ek]['freq'] = 'daily';
                }
                else if ($rule['freq']=='WEEKLY' && defined('ICSD_PRO') && ICSD_PRO) {
                    if (isset($rule['byday']) && count($rule['byday'])>1) {
                        // setup array of weekdays to compare byday with …
                        $wdconv = array( 'MO' => 1 , 'TU' => 2 , 'WE' => 3 , 'TH' => 4 , 'FR' => 5 , 'SA' => 6 , 'SU' => 7 );
                        $wdvnoc = array_flip( $wdconv );
                        // run byday to get start
                        for ($td=0; $td<7; $td++) {
                            if (in_array($wdvnoc[date('N', $fdts+86400*$td)], $rule['byday'])) {
                                $eventlist[$ek]['dtbegin'] = mktime(date('H', $eventlist[$ek]['dtbegin']),date('i', $eventlist[$ek]['dtbegin']),date('s', $eventlist[$ek]['dtbegin']),date('m', $fdts+86400*$td),date('d', $fdts+86400*$td),date('Y', $fdts+86400*$td));
                                $td = 8;
                            }
                        }
                        // setup start day
                        $sdt = $eventlist[$ek]['dtbegin']; 
                        $founddates = array();
                        foreach ($rule['byday'] AS $bdv) {
                            $checkdate = $sdt; $w = 0;
                            while ($w<$walk && $checkdate<$rule['until']) {
                                if (date('N', $checkdate)==$wdconv[$bdv]) {
                                    $founddates[] = $checkdate - $sdt;
                                    $w++;
                                }
                                $checkdate+= 86400;
                            }
                        }
                        sort($founddates);
                        // set dtend to correct timegap
                        $eventlist[$ek]['dtend'] = $eventlist[$ek]['dtbegin'] + $eventlist[$ek]['dtstamp'];
                        // run the walk
                        for ($w=0; $w<$walk; $w++) {
                            if (count($founddates)>0) {
                                $addtime = array_shift($founddates);
                                if (($eventlist[$ek]['dtbegin']+$addtime)<$rule['until']) {
                                    $timesteps[] = $addtime;
                                }
                            } else {
                                $w = $walk;
                            }
                        }
                        $eventlist[$ek]['freq'] = 'weeklymultiple';
                    } else {
                        // it's just a simple repeating by day so we can jump in 7 days steps
                        // set dtbegin to fdts with correct time
                        if ($eventlist[$ek]['dtbegin']<$fdts) {
                            $eventlist[$ek]['dtbegin'] = $eventlist[$ek]['dtbegin']+((86400*7)*(intval(ceil(($fdts-$eventlist[$ek]['dtbegin'])/(86400*7)))));
                        }
                        // set dtend to correct timegap
                        $eventlist[$ek]['dtend'] = $eventlist[$ek]['dtbegin'] + $eventlist[$ek]['dtstamp'];
                        for ($w=0; $w<$walk; $w++) {
                            if (($eventlist[$ek]['dtbegin']+$w*86400*7)<$rule['until']) {
                                $timesteps[] = $w*86400*7;
                            }
                        }
                        $eventlist[$ek]['freq'] = 'weeklyday';
                    }
                }
                else if ($rule['freq']=='MONTHLY' && defined('ICSD_PRO') && ICSD_PRO) {
                    if (isset($rule['bymonthday'])) {
                        $sdt = $eventlist[$ek]['dtbegin'];
                        // set dtbegin to fdts with correct time
                        if ($eventlist[$ek]['dtbegin']<$fdts) {
                            $eventlist[$ek]['dtbegin'] = mktime(
                                date('H', $eventlist[$ek]['dtbegin']),
                                date('i', $eventlist[$ek]['dtbegin']),
                                date('s', $eventlist[$ek]['dtbegin']),
                                date('m', $fdts),
                                date('d', $eventlist[$ek]['dtbegin']),
                                date('Y', $eventlist[$ek]['dtbegin']));
                        }
                        // set dtend to correct timegap
                        $eventlist[$ek]['dtend'] = $eventlist[$ek]['dtbegin'] + $eventlist[$ek]['dtstamp'];
                        for ($w=0; $w<$walk; $w++) {
                            $step = mktime(
                                date('H', $eventlist[$ek]['dtbegin']),
                                date('i', $eventlist[$ek]['dtbegin']),
                                date('s', $eventlist[$ek]['dtbegin']),
                                date('m', $eventlist[$ek]['dtbegin'])+$w,
                                date('d', $eventlist[$ek]['dtbegin']),
                                date('Y', $eventlist[$ek]['dtbegin'])) - $sdt;
                            if (($eventlist[$ek]['dtbegin']+$step)<$rule['until']) {
                                $timesteps[] = $step;
                            }
                        }
                        $eventlist[$ek]['freq'] = 'monthlyday';
                    }
                    else if (isset($rule['byday'])) {
                        $wdconv = array( 'MO' => 1 , 'TU' => 2 , 'WE' => 3 , 'TH' => 4 , 'FR' => 5 , 'SA' => 6 , 'SU' => 7 );
                        $sdt = $eventlist[$ek]['dtbegin'];
                        preg_match('/[0-9]/m', $rule['byday'][0], $nval);
                        preg_match('/[A-Z]{2}/m', $rule['byday'][0], $dval);
                        // compare the $fdts-month against $sdt-month to get the first month ($fm) to calculate 
                        $fm = ((date('m', $fdts)!=date('m', $sdt) && $fdts>$sdt)?date('m', $fdts):date('m', $sdt));
                        // get the timestamp of appearance
                        $returnstamp = 0; $w = 0; $timestamp = array(); 
                        while ($returnstamp<=$rule['until'] && $w<=$walk) {
                            // define the first day of month
                            $fdom = mktime(date('H', $sdt),date('i', $sdt),date('s', $sdt),($fm+$w),1,date('Y', $sdt)); 
                            $timestamp[] = $returnstamp = $this->ics_get_sdom($fdom, intval($wdconv[$dval[0]]), intval($nval[0]));
                            $w++;
                        }
                        // convert timestamps as difference to startdate in target timesteps
                        foreach ($timestamp AS $tv) {
                            if ($tv>$eventlist[$ek]['dtbegin']) {
                                $timesteps[] = $tv - $eventlist[$ek]['dtbegin'];
                            }
                        }
                        $eventlist[$ek]['freq'] = 'monthlyweekday';
                    }
                }
                else if ($rule['freq']=='YEARLY' && defined('ICSD_PRO') && ICSD_PRO) {
                    $sdt = $eventlist[$ek]['dtbegin'];
                    if ($eventlist[$ek]['dtbegin']<$fdts) {
                        $eventlist[$ek]['dtbegin'] = mktime(
                            date('H', $eventlist[$ek]['dtbegin']),
                            date('i', $eventlist[$ek]['dtbegin']),
                            date('s', $eventlist[$ek]['dtbegin']),
                            date('m', $eventlist[$ek]['dtbegin']),
                            date('d', $eventlist[$ek]['dtbegin']),
                            date('Y', $fdts));
                    }
                    // set dtend to correct timegap
                    $eventlist[$ek]['dtend'] = $eventlist[$ek]['dtbegin'] + $eventlist[$ek]['dtstamp'];
                    for ($w=0; $w<$walk; $w++) {
                        $step = mktime(
                            date('H', $eventlist[$ek]['dtbegin']),
                            date('i', $eventlist[$ek]['dtbegin']),
                            date('s', $eventlist[$ek]['dtbegin']),
                            date('m', $eventlist[$ek]['dtbegin']),
                            date('d', $eventlist[$ek]['dtbegin']),
                            date('Y', $eventlist[$ek]['dtbegin'])+$w) - $sdt;
                        if (($eventlist[$ek]['dtbegin']+$step)<$rule['until']) {
                            $timesteps[] = $step;
                        }
                    }
                    $eventlist[$ek]['freq'] = 'yearly';
                    $timeval = 86400*7;
                }
                else if (defined('ICSD_PRO') && ICSD_PRO) {
                    $eventlist[$ek]['description'].= '<hr />'.__('There seems to be an undefined RULE in calendar', $this->plugin_name);
                }
                $eventlist[$ek]['until'] = $rule['until'];
                $eventlist[$ek]['untildate'] = date('Y-m-d', $rule['until']);
                $eventlist[$ek]['steps'] = array_unique($timesteps);
            }
            if (!(isset($ev['location']))) { $eventlist[$ek]['location'] = false; }
            // if (!(isset($ev['sequence']))) { $eventlist[$ek]['sequence'] = 0; }
            if (!(isset($ev['summary']))) { $eventlist[$ek]['summary'] = ((isset($eventlist[$ek]['description']) && trim($eventlist[$ek]['description'])!='')?trim($eventlist[$ek]['description']):''); }
            if (!(isset($ev['event']))) { $eventlist[$ek]['event'] = ((isset($eventlist[$ek]['summary']) && trim($eventlist[$ek]['summary'])!='')?trim($eventlist[$ek]['summary']):''); }
            // timezone
            // prepare dtstamp for logic output
            if (($eventlist[$ek]['dtstamp'] % 86400)==0) {
                if (intval($eventlist[$ek]['dtstamp'])==86400) {
                    $eventlist[$ek]['dtend'] = false;
                }
                else {
                    $eventlist[$ek]['dtend'] = ($eventlist[$ek]['dtend']-43200);
                }
                $eventlist[$ek]['dtstamp'] = sprintf(_n('%s day', '%s days', intval(floor($eventlist[$ek]['dtstamp']/86400)), $this->plugin_name), number_format_i18n(intval(floor($eventlist[$ek]['dtstamp']/86400))));
                $eventlist[$ek]['type'] = 'day';
            } 
            else if (($eventlist[$ek]['dtstamp'] % 3600)==0 && $eventlist[$ek]['dtstamp']>=3600) {
                $eventlist[$ek]['dtstamp'] = sprintf(_n('%s hr.', '%s hrs.', intval(floor($eventlist[$ek]['dtstamp']/3600)), $this->plugin_name), number_format_i18n(intval(floor($eventlist[$ek]['dtstamp']/3600))));
                $eventlist[$ek]['type'] = 'daytime';
            } else {
                $dttmp = $eventlist[$ek]['dtstamp'];
                $eventlist[$ek]['dtstamp'] = intval(floor($dttmp/3600));
                $tmtmp = intval(($dttmp % 3600)/60);
                while (strlen($tmtmp)<2) {
                    $tmtmp = '0'.$tmtmp;
                }
                $eventlist[$ek]['dtstamp'].= ':'.$tmtmp.' '.__('hrs.', $this->plugin_name);
                $eventlist[$ek]['type'] = 'daytime';
            }

            if (!(isset($ev['dtbegin']))) { 
                unset($eventlist[$ek]); 
            } 
            else { 
                ksort($eventlist[$ek]); 
            }
        }
        // renumber eventlist to affect removed entries
        $eventlist = array_values($eventlist);
        return $eventlist;
    } 

    // get s|elected d|ay o|f m|onth
    // @since 2.0
    // input:
    // month = any timestamp in month
    // day = integer representing the date('N')
    // return:
    // timestamp
    private function ics_get_sdom ( $month , $day , $appearance ) {
        // get the [f]irst [d]ay [o]f [m]onth
        $fdom = mktime(date('H', $month),date('i', $month),date('s', $month),date('m', $month),1,date('Y', $month));
        // find the FIRST appearance of weekday
        for ($wd=0; $wd<7; $wd++) {
            if (date('N', $fdom+$wd*86400)==intval($day)) { $sdom = $fdom+$wd*86400; $wd = 10; }
        }
        // run the weeks and get the final appearance
        for ($wv=0; $wv<$appearance; $wv++) {
            $sdom+= ((86400*7)*$wv);
        }
        return $sdom;
    }

    private function ics_get_datetime ( $start , $end , $type ) {
        date_default_timezone_set($this->icsd_timezone);
        if ($type=='day') {
            $output = "<span class='ics-date-content ics-dtbegin-date-content'>".date_i18n(get_option('date_format'), $start)."</span>";
            if ($end!=false && date_i18n(get_option('date_format'), $start)!=date_i18n(get_option('date_format'), $end)) {
                $output.= "<span class='ics-separator ics-date-separator'> - </span><span class='ics-date-content ics-dtend-date-content'>".date_i18n(get_option('date_format'), $end)."</span>";
            }
        }
        else {
            $output = "<span class='ics-date-content ics-dtbegin-date-content'>".date_i18n(get_option('date_format'), $start)."</span><span class='ics-separator ics-begin-separator ics-datetime-separator'>, </span><span class='ics-time-content ics-dtbegin-time-content'>".date(get_option('time_format'), $start)."</span>";
            if ($end!=false) {
                $output.= "<span class='ics-separator ics-date-separator'> - </span>";
                if (date(get_option('date_format'), $start)!=date(get_option('date_format'), $end)) {
                    $output.= "<span class='ics-date-content ics-dtend-date-content'>".date_i18n(get_option('date_format'), $end)."</span><span class='ics-separator ics-dtend-separator ics-datetime-separator'>, </span>";
                }
                $output.= "<span class='ics-time-content ics-dtend-time-content'>".date(get_option('time_format'), $end)."</span>";
            }
        }
        return $output;
    } 

    private function ics_get_date ( $start , $end , $type ) {
        date_default_timezone_set($this->icsd_timezone);
        $output = "<span class='ics-date-content ics-dtbegin-date-content'>".date_i18n(get_option('date_format'), $start)."</span>";
        if ($end!=false && date(get_option('date_format'), $start)!=date(get_option('date_format'), $end)) {
             $output.= "<span class='ics-separator ics-date-separator'> - </span><span class='ics-date-content ics-dtend-date-content'>".date_i18n(get_option('date_format'), $end)."</span>";
        }
        return $output;
    } 
    
    private function ics_get_time ( $start , $end , $type ) {
        date_default_timezone_set($this->icsd_timezone);
        if ($type=='day') {
            $output = '';
        }
        else {
            $output = "<span class='ics-time-content ics-dtbegin-time-content'>".date(get_option('time_format'), $start)."</span>";
            if ($end!=false) {
                $output.= "<span class='ics-separator ics-date-separator'> - </span>";
                $output.= "<span class='ics-time-content ics-dtend-time-content'>".date(get_option('time_format'), $end)."</span>";
            }
        }
        return $output;
    } 
    
    // the table view
    // @since 1.0
    private function ics_display_table ( $data ) {
        
        $thead = explode(',',$data['atts']['dataset']);
        $thdesc = explode(',',$data['atts']['header']);
        if (is_array($thead) && count($thead)>0) {
            $cols = count($thead);
            $header = array();
            /* prepare header description */
            foreach ($thead AS $tk => $tv) {
                $header[$tv] = (isset($thdesc[$tk]) && trim($thdesc[$tk])!='')?trim($thdesc[$tk]):__($tv, $this->plugin_name);
            }
        } else {
            $cols = false;
        }
        
        if (!isset($data['show']) || !is_array($data['show']) || count($data['show'])<1) { $cols = false; }
        if (!isset($data['list']) || !is_array($data['list']) || count($data['list'])<1) { $cols = false; }
        
        // init return
        $return = '';

        $idselector = md5(rand(0,time()));
        if ($cols!==false) {
            $return.= '<table class="icsd-table" id="icsd-table-'.$idselector.'">';
            $return.= '<thead>';
            $return.= '<tr>';
            $return.= '<th class="icsd-numbering"></th>';
            foreach ($thead AS $tk => $tv) {
                $return.= '<th class="icsd-th icsd-th-'.trim($tv).'">'.(isset($header[$tv])?$header[$tv]:'').'</th>';
            }
            $return.= '</tr>';
            $return.= '</thead>';
            $return.= '<tbody>';
            $item = $page = $num = 0;
            foreach ($data['show'] AS $dsk => $dsv) {
                foreach ($dsv AS $dk => $dv) {
                    $num++;
                    $return.= '<tr class="icsd-page icsd-page-'.$page.'">';
                    $return.= '<td class="icsd-numbering">'.$num.'</td>';
                    foreach ($thead AS $tk => $tv) {
                        $return.= '<td class="icsd-td-'.trim($tv).'">'.(isset($data['list'][$dv][$tv])?$data['list'][$dv][$tv]:'-').'</td>';
                    }
                    $return.= '</tr>';
                    $item++;
                    if ($data['atts']['page']>0 && $item>=$data['atts']['page']) {
                        $item = 0;
                        $page++;
                    }
                }
            }
            $return.= '</tbody>';
            $return.= '</table>';

            if (intval($num)>intval($data['atts']['page'])) {
                $return.= '<ul id="icsd-pager-'.$idselector.'" class="icsd-pager">';
                for ($p=0; $p<=$page; $p++) {
                    $return.= '<li rel="'.$p.'" class="icsd-pager-item '.(($p==0)?'active':'').'">'.($p+1).'</li>';
                }
                $return.= '</ul>';
            }
            $return.= '<style>
            
            #icsd-table-'.$idselector.' .icsd-page { display: none; }
            #icsd-table-'.$idselector.' .icsd-page-0 { display: table-row; }

            </style>';
            $return.= '<script>
            
                jQuery(\'#icsd-pager-'.$idselector.' .icsd-pager-item\').on(\'click\', function() {
                    jQuery(\'#icsd-pager-'.$idselector.'\').find(\'.icsd-pager-item\').removeClass(\'active\');
                    jQuery(this).addClass(\'active\');
                    jQuery(\'#icsd-table-'.$idselector.'\').find(\'.icsd-page\').hide();
                    jQuery(\'#icsd-table-'.$idselector.'\').find(\'.icsd-page-\' + jQuery(this).attr(\'rel\')).css(\'display\', \'table-row\');
                });
            
            </script>';
        }
        
        return $return;
    }
    
    // the list view
    // @since 1.0
    private function ics_display_list ( $data ) {
        
        $thead = explode(',',$data['atts']['dataset']);
        $thdesc = explode(',',$data['atts']['header']);
        if (is_array($thead) && count($thead)>0) {
            $cols = count($thead);
            $header = array();
            /* prepare header description */
            foreach ($thead AS $tk => $tv) {
                $header[$tv] = (isset($thdesc[$tk]) && trim($thdesc[$tk])!='')?trim($thdesc[$tk]):__($tv, $this->plugin_name);
            }
        } else {
            $cols = false;
        }
        
        if (!isset($data['show']) || !is_array($data['show']) || count($data['show'])<1) { $cols = false; }
        if (!isset($data['list']) || !is_array($data['list']) || count($data['list'])<1) { $cols = false; }
        
        $return = '';
        $idselector = md5(rand(0,time()));
        if ($cols!==false) {
            $return.= '<ol class="icsd-list" id="icsd-list-'.$idselector.'" start="1">';
            $item = $page = $num = 0;
            foreach ($data['show'] AS $dsk => $dsv) {
                foreach ($dsv AS $dk => $dv) {
                    $num++;
                    $return.= '<li class="icsd-page icsd-page-'.$page.'"><ul class="icsd-list-itemset">';
                    foreach ($thead AS $tk => $tv) {
                        if (isset($data['list'][$dv][$tv]) && trim($data['list'][$dv][$tv])!='') {
                            $return.= '<li class="icsd-td-'.trim($tv).'">';
                            $return.= (isset($header[$tv])?'<span class="icsd-item-desc icsd-item-desc-'.$tv.'">'.$header[$tv].': </span>':'');
                            $return.= $data['list'][$dv][$tv];
                            $return.= '</li>';
                        }
                    }
                    $return.= '</ul></li>';
                    $item++;
                    if ($data['atts']['page']>0 && $item>=$data['atts']['page']) {
                        $item = 0;
                        $page++;
                    }
                }
            }
            $return.= '</ol>';
            if ($page>0) {
                $return.= '<ul id="icsd-pager-'.$idselector.'" class="icsd-pager">';
                for ($p=0; $p<=$page; $p++) {
                    $return.= '<li rel="'.$p.'" class="icsd-pager-item '.(($p==0)?'active':'').'">'.($p+1).'</li>';
                }
                $return.= '</ul>';
            }
            $return.= '<style>
            
            #icsd-list-'.$idselector.' .icsd-page { display: none; }
            #icsd-list-'.$idselector.' .icsd-page-0 { display: list-item; }

            </style>';
            $return.= '<script>
            
                jQuery(\'#icsd-pager-'.$idselector.' .icsd-pager-item\').on(\'click\', function() {
                    jQuery(\'#icsd-pager-'.$idselector.'\').find(\'.icsd-pager-item\').removeClass(\'active\');
                    jQuery(this).addClass(\'active\');
                    jQuery(\'#icsd-list-'.$idselector.'\').attr(\'start\', ((parseInt(jQuery(this).attr(\'rel\'))*'.$data['atts']['page'].')+1));
                    jQuery(\'#icsd-list-'.$idselector.'\').find(\'.icsd-page\').hide();
                    jQuery(\'#icsd-list-'.$idselector.'\').find(\'.icsd-page-\' + jQuery(this).attr(\'rel\')).css(\'display\', \'list-item\');
                });
            
            </script>';
        }
        
        return $return;
    }
    
    // the calendar sheet
    // @since 1.0
    private function ics_display_calendar_sheet ( $data ) {
        // for different starting days of week
        $wd = array('Sun','Mon','Tue','Wed','Thu','Fri','Sat','Sun','Mon','Tue','Wed','Thu','Fri');
        // begin output
        
        $sheet = '<table class="icsd-cal-table">';
        $sheet.= '<thead>';
        $sheet.= '<tr class="icsd-cal-table-month"><th colspan="7">'.date_i18n('F', ($data['fd']+43200)).'</th></tr>';
        $sheet.= '<tr>';
        // run $wd to display weekdays with correct beginning 
        for ($sw=get_option('start_of_week'); $sw<(get_option('start_of_week')+7); $sw++) {
            $sheet.= '<th>'.__($wd[$sw]).'</th>';
        }
        $sheet.= '</tr>';
        $sheet.= '</thead>';
        // day difference -> how much days from start of week will go on until First Day of month
        $ddiff = (intval(date('w', $data['fd']))+7-get_option('start_of_week')) % 7;
        // first day of calendar
        $fdoc = $data['fd']-(86400*$ddiff);
        // weeks in calendar
        $wic = date('t', $data['fd'])+$ddiff; while ($wic % 7 != 0) { $wic++; } $wic = $wic/7;
        $sheet.= '<tbody>';
        for ($w=0; $w<$wic; $w++) {
            $sheet.= '<tr>';
            for ($d=0; $d<7; $d++) {
                $actd = $fdoc+(86400*$d)+(86400*7*$w);
                $class = array();
                $title = array();
                // if month doesn't match
                if (date('F', $actd)!=date('F', $data['fd'])) { $class[] = 'false-month'; }
                $dhr = 0;
                foreach ($data['show'] AS $dsk => $dsv) {
                    if ($dsk>=$actd && $dsk<=($actd+86399)) {
                        $class[] = ' active-date ';
                        foreach ($dsv AS $dlk => $dlv) {
                            $datadesc = explode(",", $data['atts']['header']);
                            $datavals = explode(",", $data['atts']['header']);
                            foreach ($datavals AS $dk => $dv) {
                                $title[] = $datadesc[$dk]." : ".strip_tags($data['list'][$dlv][$dv]);
                            }
                            $dhr++;
                            if ($dhr<=count($dsv)) {
                                $title[] = '—————————————————————————————————';
                            }
                            
                        }
                    }
                }
                $sheet.= '<td '.((count($class)>0)?'class="'.implode(' ', $class).'"':'').' '.((count($title)>0)?'title="'.implode(PHP_EOL, $title).'"':'').'>'.date('j', $actd).' '.(($dhr>0)?'<sup style="display: inline-block; border: 1px solid white; top: -1.3em; width: 1.3em; height: 1.3em; line-height: 1.1em; font-size: 0.5em; border-radius: 50%; text-align: center;">'.($dhr).'</sup>':'').'</td>';
            }
            $sheet.= '</tr>';
        }
        $sheet.= '</tbody>';
        $sheet.= '</table>';
        return $sheet;
    }
    
    // the calendar view
    // @since 1.0
    private function ics_display_calendar ( $data ) {
        
        $thead = explode(',',$data['atts']['dataset']);
        $thdesc = explode(',',$data['atts']['header']);
        if (is_array($thead) && count($thead)>0) {
            $cols = count($thead);
            $header = array();
            /* prepare header description */
            foreach ($thead AS $tk => $tv) {
                $header[$tv] = (isset($thdesc[$tk]) && trim($thdesc[$tk])!='')?trim($thdesc[$tk]):__($tv, $this->plugin_name);
            }
        } else {
            $cols = false;
        }
        
        if (!isset($data['show']) || !is_array($data['show']) || count($data['show'])<1) { $cols = false; }
        if (!isset($data['list']) || !is_array($data['list']) || count($data['list'])<1) { $cols = false; }
        
        $return = '';
        $idselector = md5(rand(0,time()));
        if ($cols!==false) {
            // last check for displaying calendar pages 
            if ($data['atts']['page']==0 || $data['atts']['page']>3) { $data['atts']['page'] = 3; }
            for ($cs=0; $cs<$data['atts']['page']; $cs++) {
                $fd = mktime(0,0,0,date('m',time()+86400)+$cs,1,date('Y',time()+86400));
                $ld = mktime(23,59,59,date('m',time()+86400)+$cs+1,0,date('Y',time()+86400));
                $return.= $this->ics_display_calendar_sheet( array(
                    'fd' => $fd,
                    'ld' => $ld,
                    'atts' => $data['atts'],
                    'list' => $data['list'],
                    'show' => $data['show'],
                ) );
            }
        }
        return $return;
    }
    
    public function ics_display ( $atts ) {

        $calurl = maybe_unserialize( get_option('icsd_url') );

        // get all different ID's to get URL from
        $getid = explode(',', $atts['icsd']);
        $geturl = array();
        if (is_array($getid) && count($getid)>0) {
            foreach ($getid AS $gk => $gv) {
                if (isset($calurl[($gv-1)]) && trim($calurl[($gv-1)])!='') {
                    $geturl[] = $calurl[($gv-1)];
                }
            }
            if (count($getid)==1) {
                // if there is only a call to ONE shortcode
                $calatts = maybe_unserialize( get_option('icsd_setup') );
                if (isset($calatts[(intval($getid[0])-1)])) {
                    if (!($calatts[(intval($getid[0])-1)]['max'])) {
                        $calatts[(intval($getid[0])-1)]['max'] = 0;
                    }
                    if (!($calatts[(intval($getid[0])-1)]['repeats'])) {
                        $calatts[(intval($getid[0])-1)]['repeats'] = 3;
                    }
                    // explode the array holding the config data
                    foreach ($calatts[(intval($getid[0])-1)]['dataset'] AS $cdk => $cdv) {
                        $tmpdataset[] = $cdk;
                        $tmpheader[] = $cdv;
                    }
                    $calatts[(intval($getid[0])-1)]['id'] = intval($getid[0]);
                    $calatts[(intval($getid[0])-1)]['dataset'] = implode(',', $tmpdataset);
                    $calatts[(intval($getid[0])-1)]['header'] = implode(',', $tmpheader);
                    // replace config attributes with shortcode attributes
                    foreach ($calatts[(intval($getid[0])-1)] AS $cak => $cav) {
                        $calatts[(intval($getid[0])-1)][$cak] = (isset($atts[$cak]) && $atts[$cak]!==false)?$atts[$cak]:$cav;
                    }
                    $atts = $calatts[(intval($getid[0])-1)];
                }
            }
        }
        
        // setup the event list to display
        $eventlist = array();
        // get the base data from ics-data
        foreach ($geturl AS $guk => $guv) {
            $eventlist = array_merge($eventlist, $this->get_ics_data($guv));
        }

        // if no startdate is given, we set it to today
        if (intval($atts['startdate'])==0 || trim($atts['startdate'])=='') {
            $fdts = time();
        } else {
            $stmp = explode('-', $atts['startdate']);
            $fdts = mktime(0,0,0, (($stmp[1]>0)?$stmp[1]:1), (($stmp[2]>0)?$stmp[2]:1), $stmp[0]);
        }

        if ($atts['display']=='calendar') {
            // 2021-03-08
            // run get_ics_events to get a list returned that matches same scheme for all entries
            $atts['repeats'] = ($atts['page']*31); // calculate with max days display in calendar
            $atts['max'] = 9999999; // calculate with max days display in calendar 
            $fdts = mktime(0,0,0,date('m', $fdts),1,date('Y', $fdts));
        }

        // run eventlist against first shown date and get an eventlist returned that holds all events (even repeating dates but just the SINGLE entry)
        $eventlist = $this->get_ics_events( $eventlist , $fdts , $atts['repeats'] , $atts['max'] );

        /* create a list with all id to startdate */
        $showlist = array(); $fulllist = array(); $fk = 0;
        foreach ($eventlist AS $ek => $ev) {
            // convert single entries full list entries
            foreach ($ev['steps'] AS $tv) {
                $fulllist[$fk] = $eventlist[$ek];
                $fulllist[$fk]['dtbegin'] = $this->ics_get_datetime( ($ev['dtbegin']+$tv) , false , $ev['type'] );
                $fulllist[$fk]['dtend'] = $ev['dtend']?($this->ics_get_datetime( ($ev['dtend']+$tv) , false , $ev['type'] )):false;
                $fulllist[$fk]['datetime'] = $this->ics_get_datetime( ($ev['dtbegin']+$tv) , (($ev['dtend'])?($ev['dtend']+$tv):false) , $ev['type'] );
                $fulllist[$fk]['date'] = $this->ics_get_date( ($ev['dtbegin']+$tv), ($ev['dtend']+$tv) , $ev['type'] );
                $fulllist[$fk]['time'] = $this->ics_get_time( ($ev['dtbegin']+$tv), ($ev['dtend']+$tv) , $ev['type'] );
                ksort($fulllist[$fk]);
                $showlist[($ev['dtbegin']+$tv)][] = $fk;
                $fk++;
            }
        }
        // do asc sorting to prepare for cutting the array
        ksort($showlist);

        // reduce array to $atts['max'] entries if display is list or table
        if ($atts['display']=='table' || $atts['display']=='list') {
            foreach ($showlist AS $dk => $dv) {
                if ($dk<mktime(0,0,0,date('m'),date('d'),date('Y'))) {
                    unset($showlist[$dk]);
                }
            }
            if (intval($atts['max'])>0) {
                $showlist = array_slice($showlist, 0, (intval($atts['max'])-1), true);
            }
        }
        // sort $showlist it in sorting order given by $atts['sort']
        if ($atts['sort']=='desc') { krsort($showlist); } else { ksort($showlist); }
        // prepare array to give it to display classes
        $data = array(
            'atts' => $atts,
            'list' => $fulllist,
            'show' => $showlist
        );
        // show return by display-type if there is something to show
        // show list and calendar only in PRO
        if (count($showlist)>0) {
            if ($atts['display']=='list') {
                if (defined('ICSD_PRO') && ICSD_PRO) {
                    return $this->ics_display_list($data);
                } else {
                    return __('ICSD PRO License needed', $this->plugin_name);
                }
            }
            else if ($atts['display']=='calendar') {
                if (defined('ICSD_PRO') && ICSD_PRO) {
                    return $this->ics_display_calendar($data);
                } else {
                    return __('ICSD PRO License needed', $this->plugin_name);
                }
            }
            else {
                return $this->ics_display_table($data);
            }
        }
    }

}