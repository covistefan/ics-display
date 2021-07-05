<?php
// Exit if accessed directly
if (!defined('ABSPATH')) { exit; }

?>
<h1><?php 

esc_html_e('ICS Display', ICSD_TEXT_DOMAIN); 
if ((defined('ICSD_PRO') && ICSD_PRO===true) { ?> <sup class="cv-pro-badge">PRO</sup><?php } 

?></h1>
<p><?php esc_html_e('Manage ICS-Data', ICSD_TEXT_DOMAIN); ?></p>
<div id="icsd-tabs">
    <ul class="tablist">
        <li><a href="#tabs-1"><?php esc_html_e("URL List", ICSD_TEXT_DOMAIN); ?></a></li>
        <!-- tab for each url -->
        <?php 
        
        if (isset($icsd_url) && is_array($icsd_url)) { 
            foreach($icsd_url AS $ik => $iv) {
                echo '<li '.esc_html((isset($_POST['tablist-data']) && trim($_POST['tablist-data'])==('#tabs-'.($ik+2)))?'class="active"':'').'><a href="#tabs-'.intval($ik+2).'">'.esc_html((isset($icsd_setup[$ik]['name']) && trim($icsd_setup[$ik]['name'])!='')?$icsd_setup[$ik]['name']:'[icsd id="'.intval($ik+1).'"]').'</a></li>';
            }
        } ?>
        <!-- end tab for each url -->
        <li class="pro-tab <?php echo esc_html(isset($_POST['icsd_premium']))?'active':''; ?>"><a href="#tabs-premium"><span class="cv-pro-badge"><?php echo esc_html__("PRO", ICSD_TEXT_DOMAIN); ?></span></a></li>
        <li class="help-tab <?php echo esc_html(isset($_POST['icsd_premium']))?'active':''; ?>"><a href="#tabs-help"><span class="cv-pro-badge badge-grey"><?php echo esc_html__("Help", ICSD_TEXT_DOMAIN); ?></span></a></li>
    </ul>
    <?php 