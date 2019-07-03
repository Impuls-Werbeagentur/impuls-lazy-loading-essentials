jQuery(document).ready(function($){
	$('#upload_logo_button').click(function() {
		var send_attachment_ille = wp.media.editor.send.attachment;
		var button = $(this);
		var target = $('#logo_id');
		wp.media.editor.send.attachment = function(props, attachment){
			bildid = attachment.id;
			$(target).val(bildid);
			wp.media.editor.send.attachment = send_attachment_ille;
		};
		wp.media.editor.open(button);
	});
});