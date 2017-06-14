jQuery(document).ready(function($) {
	jQuery('.glyphicon-plus-sign').click(function(event) {
		var sectionValue = jQuery(".section-control").val();
		if (sectionValue){
			var section = '<input type="text" name="FloorplanAreas[]" class="form-control custom-section"><i class="glyphicon glyphicon-minus-sign pull-right"></i>';
			jQuery(section).insertAfter('.glyphicon-plus-sign');
			var sectionValue = jQuery(".section-control").val();
			jQuery('.section-control').nextAll().eq(1).val(sectionValue);
			jQuery(".section-control").val('');
		}else{
			jQuery('#sectionModal').modal('show');
		}
	});

	jQuery(document).on('click', '.glyphicon-minus-sign', function(event) {
		jQuery(this).prev('.custom-section').remove();
		jQuery(this).remove();
		event.preventDefault();
	});
});