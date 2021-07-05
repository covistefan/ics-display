<form id="icsd_remove" action="" method="post">
    <input type="hidden" id="icsd_url_remove" name="icsd_url_remove" />
</form>
<script>

    function icsd_remove( url , urlid ) {
        if (parseInt(urlid)>0 && confirm('<?php echo __("Remove", ICSD_TEXT_DOMAIN); ?> ”' + url + '”?')) {
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
                    <th><?php echo __("Shortcode", ICSD_TEXT_DOMAIN); ?></th>
                    <th><?php echo __("ICS Name", ICSD_TEXT_DOMAIN); ?></th>
                    <th></th>
                    <th><?php echo __("ICS URL", ICSD_TEXT_DOMAIN); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (isset($icsd_url) && is_array($icsd_url)) { foreach($icsd_url AS $ik => $iv) { ?>
            <tr <?php if (!(isset($icsd_setup[$ik]['checked'])) || isset($icsd_setup[$ik]['checked']) && intval($icsd_setup[$ik]['checked'])<(time()-86400*7)) { echo 'class="warning"'; } ?>>
                <td style="white-space: nowrap;"><?php echo '[icsd id="'.($ik+1).'"]'; ?></td>
                <td style="white-space: nowrap;"><?php echo (isset($icsd_setup[$ik]['name'])?$icsd_setup[$ik]['name']:''); ?></td>
                <td><?php echo (isset($icsd_setup[$ik]['display'])?$icsd_display_icon[($icsd_setup[$ik]['display'])]:'<span class="dashicons dashicons-editor-table"></span>'); ?></td>
                <td><?php echo implode('<wbr>', str_split($icsd_url[$ik], 7)); ?></td>
                <td style="white-space: nowrap;"><span class="dashicons dashicons-edit tabtarget" rel="#tabs-<?php echo ($ik+2); ?>"></span> <span class="dashicons dashicons-trash" onclick="icsd_remove('<?php echo ((isset($icsd_setup[$ik]['name']) && trim($icsd_setup[$ik]['name'])!='')?$icsd_setup[$ik]['name']:$icsd_url[$ik]); ?>', <?php echo ($ik+1); ?>)"></span></td>
            </tr>
            <?php } } ?>
            </tbody>
        </table>
        <?php } ?>
        <h2><?php echo __("Add URL", ICSD_TEXT_DOMAIN); ?></h2>
        <p><?php echo __("Add a new URL to an ICS-File. You can change the properties after adding the URL.", ICSD_TEXT_DOMAIN); ?></p>
        <p><input type="text" name="icsd_url_new" style="width: 50%" /></p>
        <p><input type="submit" class="button button-primary" value="<?php echo __("Add", ICSD_TEXT_DOMAIN); ?>"></p>
    </div>
</form>
<form action="" method="post">
<input type="hidden" id="tablist-data" name="tablist-data" />
    <?php if (isset($icsd_url) && is_array($icsd_url)) { foreach($icsd_url AS $ik => $iv) { 
    
    if ($icsd_setup===false) {
        // setup empty dataset
        $icsd_setup[$ik] = $icsd_setup_default;
        $icsd_setup[$ik]['url'] = $iv;
    }
    else {
        // run against default array to fill unused elements
        foreach ($icsd_setup_default AS $sdk => $sdv) {
            if (!(isset($icsd_setup[$ik][$sdk]))) {
                $icsd_setup[$ik][$sdk] = $sdv;
            }
        }
    }
    $icsd_dataset_default_tmp = $icsd_dataset_default;

    ?>
    <div class="tabcontent" id="tabs-<?php echo ($ik+2); ?>">
        <input type="hidden" name="icsd_setup[<?php echo $ik; ?>][url]" value="<?php echo $iv; ?>" />
        <?php echo $icsd_setup[$ik]['display']; ?>
        <p><input type="text" readonly="readonly" name="icsd_setup[<?php echo $ik; ?>][nurl]" value="<?php echo $iv; ?>" style="width: 50%" /></p>
        <p><?php echo __("Name", ICSD_TEXT_DOMAIN); ?></p>
        <p><input type="text" name="icsd_setup[<?php echo $ik; ?>][name]" value="<?php echo $icsd_setup[$ik]['name']; ?>" style="width: 50%" /></p>
        <p><?php echo __("Content display type", ICSD_TEXT_DOMAIN); ?></p>
        <p><select name="icsd_setup[<?php echo $ik; ?>][display]" onchange="setupPage(this.value,<?php echo $ik; ?>)">
            <option value="table" <?php echo ($icsd_setup[$ik]['display']=='table')?'selected="selected"':''; ?>><?php echo __("Table view", ICSD_TEXT_DOMAIN); ?></option>
            <option value="list" <?php echo ($icsd_setup[$ik]['display']=='list' && defined('ICSD_PRO') && ICSD_PRO===true)?'selected="selected"':''; ?> <?php echo (!(defined('ICSD_PRO')) || (defined('ICSD_PRO') && ICSD_PRO===false))?'disabled="disabled"':''; ?>><?php echo __("List view".ICSD_PRO_INFO, ICSD_TEXT_DOMAIN); ?></option>
            <option value="calendar" <?php echo ($icsd_setup[$ik]['display']=='calendar' && defined('ICSD_PRO') && ICSD_PRO===true)?'selected="selected"':''; ?> <?php echo (!(defined('ICSD_PRO')) || (defined('ICSD_PRO') && ICSD_PRO===false))?'disabled="disabled"':''; ?>><?php echo __("Calendar view".ICSD_PRO_INFO, ICSD_TEXT_DOMAIN); ?></option>
        </select></p>

        <p class="icsd_setup_page_list_<?php echo $ik; ?>"><?php echo __("Sorting", ICSD_TEXT_DOMAIN); ?></p>
        <p class="icsd_setup_page_list_<?php echo $ik; ?>"><select name="icsd_setup[<?php echo $ik; ?>][sort]">
            <option value="asc" <?php echo ($icsd_setup[$ik]['sort']=='asc')?'selected="selected"':''; ?>><?php echo __("Sort ascending", ICSD_TEXT_DOMAIN); ?></option>
            <option value="desc" <?php echo ($icsd_setup[$ik]['sort']=='desc')?'selected="selected"':''; ?>><?php echo __("Sort descending", ICSD_TEXT_DOMAIN); ?></option>
        </select></p>
        <p><?php echo __("Use start date <em>(beta)</em>", ICSD_TEXT_DOMAIN); ?></p>
        <p><input type="date" name="icsd_setup[<?php echo $ik; ?>][startdate]" value="<?php echo $icsd_setup[$ik]['startdate']; ?>" /></p>
        <p class="icsd_setup_page_calendar_<?php echo $ik; ?>"><em><?php echo __("In calendar view the startdate will be the first day of month your start date is defined for.", ICSD_TEXT_DOMAIN); ?></em></p>
        <p class="icsd_setup_page_list_<?php echo $ik; ?>"><?php echo __("Use following number of future entries to display (zero shows all, limited by one year from startdate)", ICSD_TEXT_DOMAIN); ?></p>
        <p class="icsd_setup_page_list_<?php echo $ik; ?>"><input type="number" step="1" min="0" name="icsd_setup[<?php echo $ik; ?>][max]" value="<?php echo intval($icsd_setup[$ik]['max']); ?>" /></p>
        <p class="icsd_setup_page_list_<?php echo $ik; ?>"><?php echo __("Use following number of repeating entries to display (zero shows all, only limited by number of all entries be displayed)", ICSD_TEXT_DOMAIN); ?></p>
        <p class="icsd_setup_page_list_<?php echo $ik; ?>"><input type="number" step="1" min="0" name="icsd_setup[<?php echo $ik; ?>][repeats]" value="<?php echo intval($icsd_setup[$ik]['repeats']); ?>" /></p>
        <p class="icsd_setup_page_list_<?php echo $ik; ?>"><?php echo __("Use following number of entries to display per page (zero shows all)", ICSD_TEXT_DOMAIN); ?></p>
        <p class="icsd_setup_page_calendar_<?php echo $ik; ?>"><?php echo __("Use following number of calendar pages to display (zero shows three)", ICSD_TEXT_DOMAIN); ?></p>
        <p><input type="number" step="1" min="0" id="icsd_setup_page_<?php echo $ik; ?>" name="icsd_setup[<?php echo $ik; ?>][page]" value="<?php echo intval($icsd_setup[$ik]['page']); ?>" /></p>
        <p><?php echo __("Informations to be shown in table and list view or displayed as tooltip in calendar view", ICSD_TEXT_DOMAIN); ?></p>
        <ul class="icsd-header-list">
            <?php
            
            foreach ($icsd_setup[$ik]['dataset'] AS $dsk => $dsv) {
                echo '<li class="icsd-header-item"><span class="handle dashicons dashicons-move"></span> <input type="hidden" name="icsd_setup['.$ik.'][dataset]['.$dsk.']" value="0" /><input type="checkbox" name="icsd_setup['.$ik.'][dataset]['.$dsk.']" value="1" checked="checked" /> <input type="text" placeholder="'.$dsv.'" name="icsd_setup['.$ik.'][header]['.$dsk.']" value="'.$dsv.'" /></li>';
                unset($icsd_dataset_default_tmp[$dsk]);
            }
            foreach ($icsd_dataset_default_tmp AS $ddk => $ddv) {
                echo '<li class="icsd-header-item"><span class="handle dashicons dashicons-move"></span> <input type="hidden" name="icsd_setup['.$ik.'][dataset]['.$ddk.']" value="0" /><input type="checkbox" name="icsd_setup['.$ik.'][dataset]['.$ddk.']" value="1" /> <input type="text" placeholder="'.$ddv.'" name="icsd_setup['.$ik.'][header]['.$ddk.']" value="'.$ddv.'" /></li>';
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

            setupPage( '<?php echo $icsd_setup[$ik]['display'] ?>' , '<?php echo $ik; ?>' );

        });
        
        </script>
        <p><i><?php echo __("Sort elements by drag and drop and name the header fields by yourself if you won't use our prefered names.", ICSD_TEXT_DOMAIN); ?></i></p>
        <p><input type="submit" class="button button-primary" value="<?php echo __("Save", ICSD_TEXT_DOMAIN); ?>"> <input type="button" class="button button-danger" value="<?php echo __("Remove", ICSD_TEXT_DOMAIN); ?>" onclick="icsd_remove('<?php echo ((isset($icsd_setup[$ik]['name']) && trim($icsd_setup[$ik]['name'])!='')?$icsd_setup[$ik]['name']:$icsd_url[$ik]); ?>', <?php echo ($ik+1);
 ?>)"></p>
    </div>
    <?php } } ?>
</form>