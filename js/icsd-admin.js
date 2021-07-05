var jQ = jQuery.noConflict();

function initTablist() {
    var tlo = false;
    jQ('.tabcontent').hide();
    jQ('.tablist li').each(function(){
        if (jQ(this).hasClass('active')) { 
            tlo = true;
            var t = jQ(this).find('a').attr('href');
            jQ('#tablist-data').val(t);
            jQ(t).show();
        }
    });
    if (tlo===false) {
        jQ('.tablist li').first().addClass('active');
        jQ('.tabcontent').hide();
        var t = jQ('.tablist li').first().find('a').attr('href');
        jQ('#tablist-data').val(t);
        jQ(t).show();
    }
}

jQ('document').ready(function(){
    
    initTablist();
    
    jQ('.tablist li a').click(function(e) {
        e.preventDefault();
        jQ('.tablist li').removeClass('active');
        jQ(this).parent('li').addClass('active');
        var t = jQ(this).attr('href');
        jQ('.tabcontent').hide();
        jQ('#tablist-data').val(t);
        jQ(t).show();
    });
    
    jQ('.tabtarget').click(function(e) {
        e.preventDefault();
        jQ('.tablist li').removeClass('active');
        var t = jQ(this).attr('rel');
        
        jQ('.tablist').find('a[href="' + t + '"]').parent('li').addClass('active');
        
        jQ('.tabcontent').hide();
        jQ('#tablist-data').val(t);
        jQ(t).show();
    });
    
});

