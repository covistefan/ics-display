<form id="icsd_remove" action="" method="post">
    <input type="hidden" id="icsd_url_remove" name="icsd_url_remove" />
</form>
<script>

    function icsd_remove( url , urlid ) {
        if (parseInt(urlid)>0 && confirm('<?php esc_js_e("Remove", ICSD_TEXT_DOMAIN); ?> ”' + url + '”?')) {
            jQuery('#icsd_url_remove').val(urlid);
            jQuery('#icsd_remove').submit();
        }
    }
    
    function setupPage( displaytype , itemid ) {

        if (displaytype=='calendar') {
            if (parseInt(jQuery('#icsd_setup_page_' + itemid).val())>12) {
                jQuery('#icsd_setup_page_' + itemid).val(0);
            }
            jQuery('.icsd_setup_page_calendar_' + itemid).show();
            jQuery('.icsd_setup_page_list_' + itemid).hide();
        }
        else {
            jQuery('.icsd_setup_page_calendar_' + itemid).hide();
            jQuery('.icsd_setup_page_list_' + itemid).show();
        }
    }
    
</script>
<form action="" method="post">
    <div class="tabcontent" id="tabs-1">
        <?php if(is_array($icsd_url)) { ?>
        <table class="wp-list-table widefat icsd-admin-table" style="width: 95%;">
            <thead>
                <tr>
                    <th><?php esc_html_e("Shortcode", ICSD_TEXT_DOMAIN); ?></th>
                    <th><?php esc_html_e("ICS Name", ICSD_TEXT_DOMAIN); ?></th>
                    <th></th>
                    <th><?php esc_html_e("ICS URL", ICSD_TEXT_DOMAIN); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (isset($icsd_url) && is_array($icsd_url)) { foreach($icsd_url AS $ik => $iv) { ?>
            <tr <?php if (!(isset($icsd_setup[intval($ik)]['checked'])) || isset($icsd_setup[intval($ik)]['checked']) && intval($icsd_setup[intval($ik)]['checked'])<(time()-86400*7)) { echo 'class="warning"'; } ?>>
                <td style="white-space: nowrap;">[icsd id="<?php echo intval($ik+1); ?>"]</td>
                <td style="white-space: nowrap;"><?php esc_html_e(isset($icsd_setup[intval($ik)]['name'])?$icsd_setup[intval($ik)]['name']:''); ?></td>
                <td><?php echo wp_kses((isset($icsd_setup[intval($ik)]['display'])?$icsd_display_icon[($icsd_setup[intval($ik)]['display'])]:'<span class="dashicons dashicons-editor-table"></span>'), array( 'span' => array( 'class' => array()))); ?></td>
                <td><?php echo implode('<wbr>', str_split(esc_url($icsd_url[intval($ik)]), 7)); ?></td>
                <td style="white-space: nowrap;"><span class="dashicons dashicons-edit tabtarget" rel="#tabs-<?php echo intval($ik+2); ?>"></span> <span class="dashicons dashicons-trash" onclick="icsd_remove('<?php echo ((isset($icsd_setup[intval($ik)]['name']) && trim($icsd_setup[intval($ik)]['name'])!='')?esc_js($icsd_setup[intval($ik)]['name']):esc_url($icsd_url[intval($ik)])); ?>', <?php echo intval($ik+1); ?>)"></span></td>
            </tr>
            <?php } } ?>
            </tbody>
        </table>
        <?php } ?>
        <h2><?php esc_html_e("Add URL", ICSD_TEXT_DOMAIN); ?></h2>
        <p><?php esc_html_e("Add a new URL to an ICS-File. You can change the properties after adding the URL.", ICSD_TEXT_DOMAIN); ?></p>
        <p><input type="text" name="icsd_url_new" style="width: 50%" /></p>
        <p><input type="submit" class="button button-primary" value="<?php esc_attr_e("Add", ICSD_TEXT_DOMAIN); ?>"></p>
    </div>
</form>
<form action="" method="post">
<input type="hidden" id="tablist-data" name="tablist-data" />
    <?php if (isset($icsd_url) && is_array($icsd_url)) { foreach($icsd_url AS $ik => $iv) { 
    
    if ($icsd_setup===false) {
        // setup empty dataset
        $icsd_setup[intval($ik)] = $icsd_setup_default;
        $icsd_setup[intval($ik)]['url'] = $iv;
    }
    else {
        // run against default array to fill unused elements
        foreach ($icsd_setup_default AS $sdk => $sdv) {
            if (!(isset($icsd_setup[intval($ik)][$sdk]))) {
                $icsd_setup[intval($ik)][$sdk] = $sdv;
            }
        }
    }
    $icsd_dataset_default_tmp = $icsd_dataset_default;

    ?>
    <div class="tabcontent" id="tabs-<?php echo intval($ik+2); ?>">
        <input type="hidden" name="icsd_setup[<?php echo intval($ik); ?>][url]" value="<?php echo esc_attr($iv); ?>" />
        <?php esc_html_e($icsd_setup[intval($ik)]['display']); ?>
        <p><input type="text" readonly="readonly" name="icsd_setup[<?php echo intval($ik); ?>][nurl]" value="<?php echo esc_attr($iv); ?>" style="width: 50%" /></p>
        <p><?php esc_html_e("Name", ICSD_TEXT_DOMAIN); ?></p>
        <p><input type="text" name="icsd_setup[<?php echo intval($ik); ?>][name]" value="<?php echo esc_attr($icsd_setup[intval($ik)]['name']); ?>" style="width: 50%" /></p>
        <p><?php esc_html_e("Content display type", ICSD_TEXT_DOMAIN); ?></p>
        <p><select name="icsd_setup[<?php echo intval($ik); ?>][display]" onchange="setupPage(this.value,<?php echo intval($ik); ?>)">
            <option value="table" <?php echo ($icsd_setup[intval($ik)]['display']=='table')?'selected="selected"':''; ?>><?php esc_html_e("Table view", ICSD_TEXT_DOMAIN); ?></option>
            <option value="list" <?php echo ($icsd_setup[intval($ik)]['display']=='list' && defined('ICSD_PRO') && ICSD_PRO===true)?'selected="selected"':''; ?> <?php echo (!(defined('ICSD_PRO')) || (defined('ICSD_PRO') && ICSD_PRO===false))?'disabled="disabled"':''; ?>><?php esc_html_e("List view".ICSD_PRO_INFO, ICSD_TEXT_DOMAIN); ?></option>
            <option value="calendar" <?php echo ($icsd_setup[intval($ik)]['display']=='calendar' && defined('ICSD_PRO') && ICSD_PRO===true)?'selected="selected"':''; ?> <?php echo (!(defined('ICSD_PRO')) || (defined('ICSD_PRO') && ICSD_PRO===false))?'disabled="disabled"':''; ?>><?php esc_html_e("Calendar view".ICSD_PRO_INFO, ICSD_TEXT_DOMAIN); ?></option>
        </select></p>

        <p class="icsd_setup_page_list_<?php echo intval($ik); ?>"><?php esc_html_e("Sorting", ICSD_TEXT_DOMAIN); ?></p>
        <p class="icsd_setup_page_list_<?php echo intval($ik); ?>"><select name="icsd_setup[<?php echo intval($ik); ?>][sort]">
            <option value="asc" <?php echo ($icsd_setup[intval($ik)]['sort']=='asc')?'selected="selected"':''; ?>><?php esc_html_e("Sort ascending", ICSD_TEXT_DOMAIN); ?></option>
            <option value="desc" <?php echo ($icsd_setup[intval($ik)]['sort']=='desc')?'selected="selected"':''; ?>><?php esc_html_e("Sort descending", ICSD_TEXT_DOMAIN); ?></option>
        </select></p>
        <p><?php esc_html_e("Use start date (beta)", ICSD_TEXT_DOMAIN); ?></p>
        <p><input type="date" name="icsd_setup[<?php echo intval($ik); ?>][startdate]" value="<?php echo esc_attr($icsd_setup[intval($ik)]['startdate']); ?>" /></p>
        <p class="icsd_setup_page_calendar_<?php echo intval($ik); ?>"><em><?php esc_html_e("In calendar view the startdate will be the first day of month your start date is defined for.", ICSD_TEXT_DOMAIN); ?></em></p>
        <p class="icsd_setup_page_list_<?php echo intval($ik); ?>"><?php esc_html_e("Use following number of future entries to display (zero shows all, limited by one year from startdate)", ICSD_TEXT_DOMAIN); ?></p>
        <p class="icsd_setup_page_list_<?php echo intval($ik); ?>"><input type="number" step="1" min="0" name="icsd_setup[<?php echo intval($ik); ?>][max]" value="<?php echo intval($icsd_setup[intval($ik)]['max']); ?>" /></p>
        <p class="icsd_setup_page_list_<?php echo intval($ik); ?>"><?php esc_html_e("Use following number of repeating entries to display (zero shows all, only limited by number of all entries be displayed)", ICSD_TEXT_DOMAIN); ?></p>
        <p class="icsd_setup_page_list_<?php echo intval($ik); ?>"><input type="number" step="1" min="0" name="icsd_setup[<?php echo intval($ik); ?>][repeats]" value="<?php echo intval($icsd_setup[intval($ik)]['repeats']); ?>" /></p>
        <p class="icsd_setup_page_list_<?php echo intval($ik); ?>"><?php esc_html_e("Use following number of entries to display per page (zero shows all)", ICSD_TEXT_DOMAIN); ?></p>
        <p class="icsd_setup_page_calendar_<?php echo intval($ik); ?>"><?php esc_html_e("Use following number of calendar pages to display (zero shows three)", ICSD_TEXT_DOMAIN); ?></p>
        <p><input type="number" step="1" min="0" id="icsd_setup_page_<?php echo intval($ik); ?>" name="icsd_setup[<?php echo intval($ik); ?>][page]" value="<?php echo intval($icsd_setup[intval($ik)]['page']); ?>" /></p>
        <p><?php esc_html_e("Informations to be shown in table and list view or displayed as tooltip in calendar view", ICSD_TEXT_DOMAIN); ?></p>
        <ul class="icsd-header-list">
            <?php
            
            foreach ($icsd_setup[intval($ik)]['dataset'] AS $dsk => $dsv) {
                echo '<li class="icsd-header-item"><span class="handle dashicons dashicons-move"></span> <input type="hidden" name="icsd_setup['.intval($ik).'][dataset]['.esc_attr($dsk).']" value="0" /><input type="checkbox" name="icsd_setup['.intval($ik).'][dataset]['.esc_attr($dsk).']" value="1" checked="checked" /> <input type="text" placeholder="'.esc_attr($dsv).'" name="icsd_setup['.intval($ik).'][header]['.esc_attr($dsk).']" value="'.esc_attr($dsv).'" /></li>';
                unset($icsd_dataset_default_tmp[$dsk]);
            }
            foreach ($icsd_dataset_default_tmp AS $ddk => $ddv) {
                echo '<li class="icsd-header-item"><span class="handle dashicons dashicons-move"></span> <input type="hidden" name="icsd_setup['.intval($ik).'][dataset]['.esc_attr($ddk).']" value="0" /><input type="checkbox" name="icsd_setup['.intval($ik).'][dataset]['.esc_attr($ddk).']" value="1" /> <input type="text" placeholder="'.esc_attr($ddv).'" name="icsd_setup['.intval($ik).'][header]['.esc_attr($ddk).']" value="'.esc_attr($ddv).'" /></li>';
            }        
    
            ?>
        </ul>
        <script>
        
        jQuery( function() {
            jQuery('.icsd-header-list').sortable({
                placeholder: "icsd-header-item icsd-header-item-placeholder",
                forcePlaceholderSize: true,
                handle: '.handle.dashicons-move'
            });
        })
        
        jQuery('document').ready(function(){

            setupPage( '<?php echo esc_attr($icsd_setup[intval($ik)]['display']); ?>' , '<?php echo intval($ik); ?>' );

        });
        
        </script>
        <p><i><?php esc_html_e("Sort elements by drag and drop and name the header fields by yourself if you won't use our prefered names.", ICSD_TEXT_DOMAIN); ?></i></p>
        <p><input type="submit" class="button button-primary" value="<?php esc_attr_e("Save", ICSD_TEXT_DOMAIN); ?>"> <input type="button" class="button button-danger" value="<?php esc_attr_e("Remove", ICSD_TEXT_DOMAIN); ?>" onclick="icsd_remove('<?php echo ((isset($icsd_setup[intval($ik)]['name']) && trim($icsd_setup[intval($ik)]['name'])!='')?esc_js($icsd_setup[intval($ik)]['name']):esc_url($icsd_url[intval($ik)])); ?>', <?php echo intval($ik+1); ?>)"></p>
    </div>
    <?php } } ?>
</form>