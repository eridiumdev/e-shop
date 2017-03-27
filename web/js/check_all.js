$("#checkAll").click(function() {
    $('input:checkbox').not(this).prop('checked', this.checked);
});

$("td.checkable").click(function() {
    if (!$(event.target).is('input')) {
        var $box = $(this).find('input:checkbox');
    	//$box.prop('checked', !$box.prop('checked'));
        $box.trigger('click');
    }
});

$("th.checkable").click(function() {
    if (!$(event.target).is('input')) {
        $('#checkAll').trigger('click');
    }
});
