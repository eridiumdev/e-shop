$(document).ready(function() {
	$('#overwrite_yml').modal()
	.one('click', '#confirm_button', function(e) {
		$('input#confirmed').val('true');
		//$('#batch_add').trigger('submit');
	});
});
