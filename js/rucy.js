jQuery(document).ready(function(){
    //timecomboBOx show
    jQuery('.rc-datetime-edit').on('click',function(){
        jQuery(this).hide();
        jQuery('.rc-datetime-wrap').slideDown('normal');
        return false;
    });
    // timecomboBOx close
    jQuery('.rc-datetime-update').on('click',function(){
        var year = jQuery('select[name="rc_year"]').val();
        var month = jQuery('select[name="rc_month"]').val();
        var day = jQuery('select[name="rc_day"]').val();
        var hour = jQuery('select[name="rc_hour"]').val();
        var min = jQuery('select[name="rc_minutes"]').val();
        var newDate = new Date(year, month - 1, day, hour, min);
        var now = new Date();
        var flg = false;
        if(newDate.getFullYear() != year || (1 + newDate.getMonth()) != month || newDate.getDate() != day || newDate.getMinutes() != min){
            flg = false;
        } else if (newDate.getTime() < now.getTime()) {
            flg = false;
        } else {
            flg = true;
        }
        if(flg === true){
            jQuery('.rc-datetime-wrap').removeClass('form-invalid');
        } else {
            jQuery('.rc-datetime-wrap').addClass('form-invalid');
            return false;
        }
        jQuery('.rc-datetime > b').html(year + "/" + month + "/" + day + " @ " + hour + ":" + min);
        jQuery('#rc_year_cr').val(year);
        jQuery('#rc_month_cr').val(month);
        jQuery('#rc_day_cr').val(day);
        jQuery('#rc_hour_cr').val(hour);
        jQuery('#rc_minutes_cr').val(min);
        jQuery('.rc-datetime-wrap').slideUp('normal');
        jQuery('.rc-datetime-edit').show();
        return false;
    });
    // cancel
    jQuery('.rc-datetime-cancel').on('click',function(){
        var year = jQuery('#rc_year_cr').val();
        var month = jQuery('#rc_month_cr').val();
        var day = jQuery('#rc_day_cr').val();
        var hour = jQuery('#rc_hour_cr').val();
        var min = jQuery('#rc_minutes_cr').val();
        jQuery('.rc-datetime > b').html(year + "/" + month + "/" + day + " @ " + hour + ":" + min);
        jQuery('select[name="rc_year"]').val(year);
        jQuery('select[name="rc_month"]').val(month);
        jQuery('select[name="rc_day"]').val(day);
        jQuery('select[name="rc_hour"]').val(hour);
        jQuery('select[name="rc_minutes"]').val(min);
        jQuery('.rc-datetime-wrap').slideUp('normal');
        jQuery('.rc-datetime-edit').show();
        return false;
    });
    // reservation feature image uploader
    window.original_send_to_editor = window.send_to_editor;
    window.send_to_editor = function(html){
        fileurl = jQuery('img', html).attr('src');
        jQuery('#rc_feature_image').val(fileurl);
        tb_remove();
        if(jQuery('#rc_feature_image_upload').hasClass('has_image')){
            jQuery('.rc_feature_image').attr('src', fileurl);
            jQuery('.rc_remove_feature_image').show();
        } else {
            var img = jQuery('<img>').attr('src',fileurl).addClass('rc_feature_image size-thumbnail');
            jQuery('#rc_feature_image_upload').addClass('has_image').html(img);
            jQuery('.rc_remove_feature_image').show();
        }
    };
    if(jQuery('#rc_feature_image_upload').hasClass('has_image')){
        jQuery('.rc_remove_feature_image').show();
    }
    jQuery('.rc_remove_feature_image').on('click', function(){
        jQuery('#rc_feature_image').val('');
        var replace_text = jQuery('#rc_feature_image_upload').attr('title');
        jQuery('#rc_feature_image_upload').html(replace_text);
        jQuery(this).hide();
        return false;
    });
});

