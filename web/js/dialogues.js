// Confirm delete user dialogue
$('input[name="delete"]').on('click', function(e) {
	var $btn = $(this);
	var $form = $btn.closest('form');
	e.preventDefault();
	$('#confirm_delete').modal()
	.one('click', '#confirm_button', function(e) {
		$("form").each(function(){
    		$(this).find('input:checkbox').prop('checked', false);
		});
		var $box = $btn.parent().next().find('input:checkbox');
		$box.prop('checked', true);
		$form.trigger('submit');
	});
});

// Confirm delete selected users dialogue
$('input[name="delete_selected"]').on('click', function(e) {
	var $form = $(this).closest('form');
	e.preventDefault();
	$('#confirm_delete_selected').modal()
	.one('click', '#confirm_button', function(e) {
		$form.trigger('submit');
	});
});
