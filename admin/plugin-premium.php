        <?php if (!(defined('ICSD_PRO')) || (defined('ICSD_PRO') && ICSD_PRO===false)) { ?>
            <div class="tabcontent" id="tabs-premium">
                <form action="" method="post">
                    <h2><?php echo __("Activate PRO", ICSD_TEXT_DOMAIN); ?></h2>
                    <p><?php echo __("Enter PRO version code", ICSD_TEXT_DOMAIN); ?></p>
                    <p><input type="text" name="icsd_premium" style="width: 50%" /></p>
                    <p><input type="submit" class="button button-primary" value="<?php echo __("Submit", ICSD_TEXT_DOMAIN); ?>"></p>
                </form>
                <p><?php echo __("Buy PRO version.", ICSD_TEXT_DOMAIN); ?></p>
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                    <input type="hidden" name="cmd" value="_s-xclick">
                    <input type="hidden" name="hosted_button_id" value="WH844LQQHAQQG">
                    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                    <img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1">
                </form>
            </div>
        <?php } else { ?>
            <div class="tabcontent" id="tabs-premium">
                <h2><?php echo __("PRO Information", ICSD_TEXT_DOMAIN); ?></h2>
                <p><?php printf(__("Your PRO version will end on %s.", ICSD_TEXT_DOMAIN), date_i18n(get_option('date_format'), ICSD_PRO_TIME)); ?></p>
            </div>
        <?php } ?>
