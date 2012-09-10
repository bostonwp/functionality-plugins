(function($, exports, undefined) {
    $(document).ready(function() {
        
        $('.restore_snapshot').click(function() {
            var snapshot = $(this).parent().find('select').val(), type = $(this).parent().parent().data('type');
            console.log(snapshot, type);
            $.post(ajaxurl, {action: 'restore_wp_config_snapshot', snapshot_type: type, snapshot_name: snapshot}, function(r) {
                var res = $.parseJSON(r);
                if (res.status !== 'success' && res.message) return alert(res.message);
                return window.location.reload();
            });
        });
        
        $('.take_snapshot').click(function() {
            var $el = $(this), snapshot = $(this).parent().find('.new_snapshot_name').val(), type = $(this).parent().parent().data('type');
            if (snapshot.trim() === '') return alert('Please enter a name for the new snapshot.');
            $(this).parent().find('.new_snapshot_name').val('');
            $.post(ajaxurl, {action: 'add_wp_config_snapshot', snapshot_type: type, snapshot_name: snapshot}, function(r) {
                var res = $.parseJSON(r);
                if (res.status !== 'success' && res.message) return alert(res.message);
                $el.parent().parent().find('.wp_settings_snapshot_select')
                    .append('<option selected="selected" value="'+snapshot+'">'+snapshot+'</option>').val(snapshot);
                return 1;
            });
            return 1;
        });
    });
}(jQuery, window))