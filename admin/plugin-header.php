<?php
// Exit if accessed directly
if (!defined('ABSPATH')) { exit; }

echo "<h1>".__('ICS Display', ICSD_TEXT_DOMAIN).((defined('ICSD_PRO') && ICSD_PRO===true)?' <sup class="cv-pro-badge">PRO</sup>':'')."</h1>";
echo "<p>".__('Manage ICS-Data', ICSD_TEXT_DOMAIN)."</p>";

?>
<div id="icsd-tabs">
    <ul class="tablist">
        <li><a href="#tabs-1"><?php echo __("URL List", ICSD_TEXT_DOMAIN); ?></a></li>
        <!-- tab for each url -->
        <?php if (isset($icsd_url) && is_array($icsd_url)) { foreach($icsd_url AS $ik => $iv) {
            echo '<li '.((isset($_POST['tablist-data']) && trim($_POST['tablist-data'])==('#tabs-'.($ik+2)))?'class="active"':'').'><a href="#tabs-'.($ik+2).'">'.((isset($icsd_setup[$ik]['name']) && trim($icsd_setup[$ik]['name'])!='')?$icsd_setup[$ik]['name']:'[icsd id="'.($ik+1).'"]').'</a></li>';
        }} ?>
        <!-- end tab for each url -->
        <li class="pro-tab <?php echo (isset($_POST['icsd_premium']))?'active':''; ?>"><a href="#tabs-premium"><span class="cv-pro-badge"><?php echo __("PRO", ICSD_TEXT_DOMAIN); ?></span></a></li>
        <li class="help-tab <?php echo (isset($_POST['icsd_premium']))?'active':''; ?>"><a href="#tabs-help"><span class="cv-pro-badge badge-grey"><?php echo __("Help", ICSD_TEXT_DOMAIN); ?></span></a></li>
    </ul>
    <?php 