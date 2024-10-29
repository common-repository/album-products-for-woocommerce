function album_submit(e){
	let ths = e;

	ths.parents('form').append( '<input type="hidden" name="' + ths.attr('name') + '" value="' + ths.val() + '">' );
	ths.parents('form').submit();
}
