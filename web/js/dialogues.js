// Confirm delete dialogue
$('input[name="delete"]').on('click', function(e) {
	var $btn = $(this);
	var $form = $btn.closest('form');
	e.preventDefault();
	$('#deleteModal').modal()
	.one('click', '#confirm_button', function(e) {
		$("form").each(function(){
    		$(this).find('input:checkbox').prop('checked', false);
		});
		var $box = $btn.parent().next().find('input:checkbox');
		$box.prop('checked', true);
		$form.trigger('submit');
	});
});

// Confirm delete selected dialogue
$('input[name="deleteSelected"]').on('click', function(e) {
	var $form = $(this).closest('form');
	e.preventDefault();
	$('#deleteAllModal').modal()
	.one('click', '#confirm_button', function(e) {
		$form.trigger('submit');
	});
});

// Confirm delete selected dialogue
$('input[name="deleteSingle"]').on('click', function(e) {
	var $btn = $(this);
	var $form = $btn.closest('form');
	e.preventDefault();
	$('#deleteModal').modal()
	.one('click', '#confirm_button', function(e) {
		$("form").each(function(){
    		$(this).find('input:checkbox').prop('checked', false);
		});
		var $box = $btn.siblings('input:checkbox');
		$box.prop('checked', true);
		$form.trigger('submit');
	});
});
