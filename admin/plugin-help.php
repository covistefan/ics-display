<div class="tabcontent" id="tabs-help">
    <h2><?php echo __("ICSD Help", ICSD_TEXT_DOMAIN); ?></h2>
    <p><?php echo __("To embed the ICS-data you can simply configurate the ICS-item, copy the shortcode and add it to your page or use it with the block editor.", ICSD_TEXT_DOMAIN); ?></p>
    <h3><?php echo __("Field data handling", ICSD_TEXT_DOMAIN); ?></h3>
    <ul>
        <li><?=__('date', ICSD_TEXT_DOMAIN); ?>: <?=__('This field will show up the date formatted by your wordpress date options.', ICSD_TEXT_DOMAIN); ?> <?=__('Can be a range.', ICSD_TEXT_DOMAIN); ?></li>
        <li><?=__('time', ICSD_TEXT_DOMAIN); ?>: <?=__('This field will show up the time formatted by your wordpress time options.', ICSD_TEXT_DOMAIN); ?> <?=__('Can be a range.', ICSD_TEXT_DOMAIN); ?></li>
        <li><?=__('dtbegin', ICSD_TEXT_DOMAIN); ?>: <?=__('This field will show up the date and time formatted by your wordpress date and time options.', ICSD_TEXT_DOMAIN); ?></li>
        <li><?=__('dtend', ICSD_TEXT_DOMAIN); ?>: <?=__('This field will show up the date and time formatted by your wordpress date and time options.', ICSD_TEXT_DOMAIN); ?></li>
        <li><?=__('dtstamp', ICSD_TEXT_DOMAIN); ?>: <?=__('This field will show up the time in hours and minutes your event will last.', ICSD_TEXT_DOMAIN); ?></li>
    </ul>
    <h3><?php echo __("ICSD advanced shortcodes", ICSD_TEXT_DOMAIN); ?></h3>
    <p><?php echo __("If you want to combine multiple ICS-datasets, you can manage the style within the configuration in shortcode-tag.", ICSD_TEXT_DOMAIN); ?></p>
    <p><?php echo __("Following options are avaiable:", ICSD_TEXT_DOMAIN); ?></p>
    <p><code><?php echo __('id="id1,id2,idx"', ICSD_TEXT_DOMAIN); ?></code></p>
    <p><code><?php echo __('display="table|list|calendar"', ICSD_TEXT_DOMAIN); ?></code></p>
    <p><?php echo __("List and calendar view are only supported in PRO.", ICSD_TEXT_DOMAIN); ?></p>
    <p><code>max="<em>int</em>"</code></p>
    <p><code><?php echo __('page="int"', ICSD_TEXT_DOMAIN); ?></code></p>
    <p><?php echo __('Next attributes are only used with display "table" or "list".', ICSD_TEXT_DOMAIN); ?></p>
    <p><code><?php echo __('sort="asc|desc"', ICSD_TEXT_DOMAIN); ?></code></p>
    <p><code><?php echo __('dataset="elem1,elem2,elemx"', ICSD_TEXT_DOMAIN); ?></code></p>
    <p><code><?php echo __('header="name1,name2,namex"', ICSD_TEXT_DOMAIN); ?></code></p>
</div>