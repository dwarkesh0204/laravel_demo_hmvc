@extends('ips::layouts.app')
@section('content')
<div class="container">
	@if (count($errors) > 0)
		<div class="alert alert-danger">
			<strong>Whoops!</strong> There were some problems with your input.<br><br>
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif
	<div class="row">
		<div class="panel panel-default">
			<div class="panel-heading">Add Floorplan</div>
			<div class="panel-body">
				 <div class="col-md-6 col-md-offset-3">
				   {!! Form::open(array('route' => 'floorplan.store','method'=>'POST', 'files' => true)) !!}
					<div class="form-group">
						{!! Form::label('name', 'Name:', ['class' => 'control-label']) !!}
						{!! Form::text('name', null, ['class' => 'form-control']) !!}
					</div>
					<div class="form-group">
						{!! Form::label('image', 'Image:', ['class' => 'control-label']) !!}
						{!! Form::file('image') !!}
					</div>
					<div class="form-group">
						{!! Form::label('width', 'Width (Feet):', ['class' => 'control-label']) !!}
						{!! Form::text('width', null, ['class' => 'form-control']) !!}
					</div>
					<div class="form-group">
						{!! Form::label('height', 'Height (Feet):', ['class' => 'control-label']) !!}
						{!! Form::text('height', null, ['class' => 'form-control']) !!}
					</div>
					<div class="form-group section-floorplan">
						{!! Form::label('areas', 'Sections:', ['class' => 'control-label']) !!}
						{!! Form::text('areas', null, ['class' => 'form-control section-control']) !!}
						<i class="glyphicon glyphicon-plus-sign pull-right"></i>
					</div>
					<div class="form-group">
						<div class="pull-right">
							{!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
							<a href="{{ route('floorplan.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
						</div>
					</div>
				   {!! Form::close() !!}
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="sectionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Warning.</h4>
			</div>
			<div class="modal-body">
				 <p>Please add section name.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<script>
	jQuery(document).ready(function($) {
		jQuery('.glyphicon-plus-sign').click(function(event) {
			var sectionValue = jQuery(".section-control").val();
			if (sectionValue)
			{
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
</script>
@endsection
