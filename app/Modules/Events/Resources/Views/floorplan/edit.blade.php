@extends('events::layouts.app')
@section('javascript')
{{ Html::script('/js/custom.js') }}
<style type="text/css">
.form-control.section-control {
    width: 90%;
}
.form-control.section-control, .glyphicon-plus-sign {
    display: inline-block;
}
.glyphicon-plus-sign {
    cursor: pointer;
    margin-right: 30px;
    margin-top: 5px;
}
.glyphicon-minus-sign {
    display: inline-block;
    cursor: pointer;
    margin-right: 30px;
    margin-top: 18px;
}
.custom-section {
    margin-top: 10px;
    width: 90%;
    display: inline-block;
}
.displayLocation {
    width: 100px;
}

#canvas {
    z-index: 1;
}
#myCanvas {
    z-index: 3;
}
#canvasRange{
    z-index: 2;
}
#canvas-wrapper {
    position: relative;
}
.tool-selector{
    margin-bottom: 10px;
}
</style>
@endsection
@section('content')
    {{--<div><div id="page-content-wrapper" >--}}
            <div class="row">
                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                @if(Session::has('edit_message'))
                    <div class="alert alert-success">
                        {{ Session::get('edit_message') }}
                    </div>
                @endif
                    <div class="col-md-12 tool-selector">
                        <div class="col-md-4">
                            X : <span id="live_x"></span>
                            Y : <span id="live_y"></span>
                            <img id="apply_loader" class="pull-left" src="{{url('/').'/uploads/saving_loader.gif'}}" style="display:none;">
                        </div>
                        <div class="col-md-4">
                            <button id="beacon-selector" type="button" class="btn btn-default" onclick="select_type('beacon')">Beacon</button>
                            <button id="path-selector" type="button" class="btn btn-default" onclick="select_type('path')">Path</button>
                            <button id="calibrator-selector" type="button" class="btn btn-default" onclick="select_type('calibrator')">Calibrator</button>
                            <button id="poi-selector" type="button" class="btn btn-default" onclick="select_type('poi')">POI</button>
                            <button id="gateway-selector" type="button" class="btn btn-default" onclick="select_type('gateway')">Gateway</button>
                        </div>
                        <div class="col-md-4">
                            <div class="pull-right">
                                <button type="button" id="select_delete" class="btn btn-primary" onclick="isDrag=0;isDelete=true;start_drawing=0;add_calibrator=0;add_poi=0;add_gateway=0;add_path=0">Select</button>
                                <button type="button" class="btn btn-success" onclick="isDrag=0;add_gateway=0;add_calibrator=0;add_path=0;isDelete=0;start_drawing=0;save_nodes_path()">Save</button>
                                <button type="button" class="btn btn-success" onclick="isDrag=0;add_gateway=0;add_calibrator=0;add_path=0;isDelete=0;start_drawing=0;jQuery('#EXPORTModal').modal('show');"><span class="glyphicon glyphicon-export"></span>Export</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="col-md-4"></div>
                        <div class="col-md-4">
                            <div id="beacon_action">
                                <button type="button" class="btn btn-default sub-button" onclick="isDrag=0;add_calibrator=0;add_path=0;add_poi=0;add_gateway=0;add_beacon()">Add</button>
                                <button type="button" class="btn btn-default sub-button" onclick="adjust_position('beacon');">Adjust</button>
                                <button type="button" class="btn btn-default sub-button" onclick="isDrag=0;drawRange('beacon')">Show Range</button>
                            </div>
                            <div id="path_action" style="display: none">
                                <button type="button" class="btn btn-default sub-button" onclick="isDrag=0;add_path=1;add_calibrator=0;add_gateway=0;add_poi=0;">New Path</button>
                                <button type="button" class="btn btn-default sub-button" onclick="adjust_position('path');">Adjust</button>
                            </div>
                            <div id="calibrator_action" style="display: none">
                                <button type="button" class="btn btn-default sub-button" onclick="isDrag=0;start_drawing=0;add_path = 0;add_calibrator=1;add_gateway=0;add_poi=0;">Add Point</button>
                                <button type="button" class="btn btn-default sub-button" onclick="adjust_position('calibrator');">Adjust</button>
                                <button type="button" class="btn btn-default sub-button" onclick="isDrag=0;drawRange('calibrator')">Show Range</button>
                            </div>
                            <div id="poi_action" style="display: none">
                                <button type="button" class="btn btn-default sub-button" onclick="isDrag=0;start_drawing=0;add_path = 0;add_calibrator=0;add_gateway=0;add_poi=1;">Add POI</button>
                                <button type="button" class="btn btn-default sub-button" onclick="adjust_position('poi');">Adjust</button>
                            </div>
                            <div id="gateway_action" style="display: none">
                                <button type="button" class="btn btn-default sub-button" onclick="isDrag=0;start_drawing=0;add_path = 0;add_calibrator=0;add_poi=0;add_gateway=1;">Add Gateway</button>
                                <button type="button" class="btn btn-default sub-button" onclick="adjust_position('gateway');">Adjust</button>
                            </div>
                        </div>
                        <div class="col-md-4"></div>
                    </div>
                {{--<div class="panel panel-default">--}}

                    <div class="panel-body">

                        <div class="col-md-12">
                            <div id="canvas-wrapper" style="height:{{$ImageHeight}}px; width:{{$ImageWidth}}px;">
                                <canvas id="canvasRange" height="{{$ImageHeight}}" width="{{$ImageWidth}}" style="z-index: 2;
position:absolute;
left:0px;
top:0px;
"></canvas>
                                <canvas id="canvas" height="{{$ImageHeight}}" width="{{$ImageWidth}}" style="z-index: 1;
position:absolute;
left:0px;
top:0px;
"></canvas>
                                <canvas id="myCanvas" height="{{$ImageHeight}}" width="{{$ImageWidth}}" style="z-index: 3;
position:absolute;
left:0px;
top:0px;
"></canvas>
                            </div>
                        </div>
                        <div class="floorplan-form">
                            {!! Form::model('',['method' => 'PATCH','route' => ['floorplans.update', $FloorplanData->id],'files' => true])!!}
                            <div class="col-md-12">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('name', 'Name:', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', $FloorplanData->name, ['class' => 'form-control']) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('image', 'Image:', ['class' => 'control-label']) !!}
                                        {!! Form::file('image')!!}
                                    </div>
                                </div>
                            <div>
                            <div class="col-md-12">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('width', 'Width (Feet):', ['class' => 'control-label']) !!}
                                        {!! Form::text('width', $FloorplanData->width, ['class' => 'form-control']) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('height', 'Length (Feet):', ['class' => 'control-label']) !!}
                                        {!! Form::text('height', $FloorplanData->height, ['class' => 'form-control']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('areas', 'Sections:', ['class' => 'control-label']) !!}
                                    {!! Form::text('areas[]', null, ['class' => 'form-control section-control']) !!}
                                    <i class="glyphicon glyphicon-plus-sign pull-right"></i>
                                    @foreach($AreasArray as $Area)
                                        {!! Form::text('areas[]', $Area, ['class' => 'form-control custom-section']) !!}
                                        <i class="glyphicon glyphicon-minus-sign pull-right"></i>
                                    @endforeach
                                </div>
                                <div class="form-group">
                                    <div class="pull-right">
                                        {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
                                        <a href="{{ route('floorplans.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                                    </div>
                                    <input type="hidden" id="locationx" name="beaconLocationX" value="">
                                    <input type="hidden" id="locationy" name="beaconLocationY" value="">
                                    <input type="hidden" id="displayLocationX" name="displayLocationX" value="">
                                    <input type="hidden" id="displayLocationY" name="displayLocationY" value="">
                                    <input type="hidden" id="id" name="BeaconID" value="">
                                    <input type="hidden" id="SpotBeaconID" name="SpotBeaconID" value="">
                                    <input type="hidden" id="BeaconArea" name="BeaconArea" value="">

                                    <input type="hidden" id="NodeID" name="NodeID" value="">
                                    <input type="hidden" id="NodeLocationX" name="NodeLocationX" value="">
                                    <input type="hidden" id="NodeLocationY" name="NodeLocationY" value="">
                                    <input type="hidden" id="NodeDisplayLocationX" name="NodeDisplayLocationX" value="">
                                    <input type="hidden" id="NodeDisplayLocationY" name="NodeDisplayLocationY" value="">
                                    <input type="hidden" id="PoiNodeID" name="PoiNodeID" value="">
                                    <input type="hidden" id="NodeType" name="NodeType" value="">

                                    <input type="hidden" id="main-node" value="">

                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                {{--</div>--}}
            </div>
        {{--</div>
    </div>--}}

    <!-- POI select Modal -->
    <div class="modal fade" id="POIModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Select POI Area</h4>
                </div>
                <div class="modal-body">

                <div class="form-group">
                {{ Form::select('area_list', (['0' => 'Select an Area'] + array_combine(explode(',',$FloorplanData->areas), explode(',',$FloorplanData->areas))),NULL,array('id'=>'area_list','class' => 'form-control')) }}
                </div>
                <div class="form-group">
                    {{ Form::select('node_list', (['0' => 'Select an Node'] + $FloorNodes_dropdown),NULL,array('id'=>'node_list','class' => 'form-control','data-live-search'=>'true')) }}
                </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="save_poi_area();">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Export select Modal -->
    <div class="modal fade" id="EXPORTModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Select Export Data</h4>
                </div>
                <div class="modal-body">
                    <div class="col-md-12">
                        <div class="col-md-4"><button type="button" class="btn btn-default" onclick="export_data('beacon');">Beacons</button></div>
                        <div class="col-md-4"><button type="button" class="btn btn-default" onclick="export_data('path');">Path</button></div>
                        <div class="col-md-4"><button type="button" class="btn btn-default" onclick="export_data('calibrator');">Calibrator</button></div>
                    </div>
                    <div class="col-md-12">
                        <div class="col-md-4"><button type="button" class="btn btn-default" onclick="export_data('poi');">POI</button></div>
                        <div class="col-md-4"><button type="button" class="btn btn-default" onclick="export_data('gateway');">Gateway</button></div>
                        <div class="col-md-4"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="exp_data" type="button" class="btn btn-default"></button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Section Warning Modal -->
    <div class="modal fade" id="sectionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
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
<script type="text/javascript">
    var gatewayobj='';
    var gatewayobj_array = [];
    function ajaxGetAvailableGateway()
    {
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $('#apply_loader').show();
         $.ajax({
            url: '{{url('events/floorplans/ajaxGetAvailableGateWay')}}',
            type: 'POST',
            async:false,
            data: {
                _token: CSRF_TOKEN,
                Gate_Ways: gatewayobj_array,
                floorPlan:{{$id}},/*future enhancement*/
            },
            dataType: 'json',
            success: function (response) {
                if(response.success)
                {
                    if (response.device_id != 0)
                    {
                        if(checkForValue(gateways, gatewayobj))
                        {
                            alert('Already Selected this device');
                            gatewayobj = '';
                        }
                        else
                        {
                            deviceid = response.device_id
                            gatewayobj_array.push(deviceid);
                            gatewayobj = response.device_id;
                        }
                        $('#apply_loader').hide();
                    }
                }
                else
                {
                    $('#apply_loader').hide();
                    alert(response.message);
                    gatewayobj = '';
                }
            }
        });
        return gatewayobj;

    }

    //adjust position mode
    function adjust_position(type)
    {
        switch(type) {
            case 'beacon':
                adjust_beacon = true;
                adjust_node = false;
                adjust_calibrator = false;
                adjust_poi = false;
                adjust_gateway = false;
                break;
            case 'path':
                adjust_beacon = false;
                adjust_node = true;
                adjust_calibrator = false;
                adjust_poi = false;
                adjust_gateway = false;
                break;
            case 'calibrator':
                adjust_beacon = false;
                adjust_node = false;
                adjust_calibrator = true;
                adjust_poi = false;
                adjust_gateway = false;
                break;
            case 'poi':
                adjust_beacon = false;
                adjust_node = false;
                adjust_calibrator = false;
                adjust_poi = true;
                adjust_gateway = false;
                break;
            case 'gateway':
                adjust_beacon = false;
                adjust_node = false;
                adjust_calibrator = false;
                adjust_poi = true;
                adjust_gateway = true;
                break;
        }
        isDrag=true;
        start_drawing=0;
        add_path=0;
        add_calibrator=0;
        add_poi=0;
        add_gateway=0;
    }

    //tool selector
    function select_type(type)
    {
        $('#beacon-selector').removeClass('btn-primary');
        $('#path-selector').removeClass('btn-primary');
        $('#calibrator-selector').removeClass('btn-primary');
        $('#poi-selector').removeClass('btn-primary');
        $('#gateway-selector').removeClass('btn-primary');
        $('#'+type+'-selector').addClass('btn-primary');

        $('#beacon_action').css('display','none');
        $('#path_action').css('display','none');
        $('#calibrator_action').css('display','none');
        $('#poi_action').css('display','none');
        $('#gateway_action').css('display','none');
        $('#'+type+'_action').css('display','');
    }

    //Add beacon to canvas
    var added_beacons = [];
    function add_beacon(){
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $('#apply_loader').show();
        $.ajax({
            url: '{{url('events/floorplans/ajaxAddBeacon')}}',
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                floorPlan:{{$id}},
                added_beacons:added_beacons,
            },
            dataType: 'json',
            success: function (response) {
                if(response.success)
                {
                    if (response.id != 0)
                    {
                        beacons.push({
                            x: 25,
                            y: 20,
                            radius: beaconRadius,
                            tip:response.name,
                            id:response.id,
                            macid:response.macid,
                            temp: 1
                        });
                        draw_all();
                        beacon_macid = response.macid
                        added_beacons.push(beacon_macid);
                        $('#apply_loader').hide();
                    }
                }
                else
                {
                   alert(response.message);
                   $('#apply_loader').hide();
                }
            }
        })
    };


    //Beacon range
    function drawRange(type)
    {
        var canvas_range = document.getElementById('canvasRange');
        var ctx = canvas_range.getContext('2d');

        //if either ranges are active deactive them
        //clear range
        ctx.clearRect(0, 0, cw, ch);
        LoadPlanImage();
        if(type == 'beacon')
        {
            drawCalibratorRange = false;
        }
        if(type == 'calibrator')
        {
            drawBeaconRange = false;
        }

        if(drawBeaconRange == false && drawCalibratorRange == false)
        {
            if(type == 'beacon')
            {
                //create beacon range
                drawBeaconRange = true;
                for(i=0;i<beacons.length;i++)
                {
                    ctx.beginPath();
                    ctx.arc(beacons[i].x-15,beacons[i].y-10, BeaconRangeradius , 0, 2 * Math.PI);
                    ctx.lineWidth = 1;
                    ctx.strokeStyle = 'green';
                    ctx.stroke();
                }
            }

            if(type == 'calibrator')
            {
                //create beacon range
                drawCalibratorRange = true;
                for(i=0;i<calibrators.length;i++)
                {
                    ctx.beginPath();
                    ctx.arc(calibrators[i].x-15,calibrators[i].y-10, CalibratorRangeradius , 0, 2 * Math.PI);
                    ctx.lineWidth = 1;
                    ctx.strokeStyle = 'green';
                    ctx.stroke();
                }
            }

        }
        else
        {
            if(type == 'beacon')
            {
                drawBeaconRange = false;
            }

            if(type == 'calibrator')
            {
                drawCalibratorRange = false;
            }

            //clear range
            ctx.clearRect(0, 0, cw, ch);
            LoadPlanImage();
        }
    }

    //update poi array with area
    function save_poi_area()
    {
        var selected_area = jQuery('#area_list').val();
        var selected_node = jQuery('#node_list').val();
        for (var i = 0; i < poi_draw_count; i++)
        {
            pois[pois.length-1-i]['area']= selected_area;
            pois[pois.length-1-i]['node']= selected_node;
        }
        jQuery('#POIModal').modal('hide');
        poi_draw_count = 0;
    }


    function export_data(type)
    {
        var canvas_range = document.getElementById('myCanvas');
        var ctx = canvas_range.getContext('2d');

        ctx.clearRect(0, 0, cw, ch);
        var img1 = new Image();
        img1.src = '<?php echo url('/') . $FloorplanData->image; ?>';
        img1.onload = function () {
            //draw background image
            ctx.drawImage(img1, 0, 0);
            if(type == 'beacon') {

                //draw beacons
                draw_circle(beacons, beaconRadius, beacon_color);
                for (var i = 0; i < beacons.length; i++) {
                    var beacon = beacons[i];
                    //create Tooltips
                    ctx.fillStyle = tooltip_color;
                    ctx.fillText(beacon.tip, beacon.x - beacon.radius, beacon.y - beacon.radius);
                }
            }

            if(type == 'calibrator')
            {
                //draw calibrator
                draw_circle(calibrators,calibratorRadius,calibrator_color);
                for (var i = 0; i < calibrators.length; i++) {
                    var beacon = calibrators[i];
                    //create Tooltips
                    ctx.fillStyle = tooltip_color;
                    ctx.fillText(beacon.tip, beacon.x - beacon.radius, beacon.y - beacon.radius);
                }
            }

            if(type == 'gateway')
            {
                //draw calibrator
                draw_circle(gateways,gatewayRadius,gateway_color);
                for (var i = 0; i < gateways.length; i++) {
                    var beacon = gateways[i];
                    //create Tooltips
                    ctx.fillStyle = tooltip_color;
                    ctx.fillText(beacon.tip, beacon.x - beacon.radius, beacon.y - beacon.radius);
                }
            }

            if(type == 'path')
            {
                //drawlines
                for (var i = 0; i < relation.length; i++) {
                    var line_start = circles[relation[i].node];
                    var line_end = circles[relation[i].rel_node];
                    draw_line(line_start.x-15,line_start.y-10,line_end.x-15,line_end.y-10);
                }
                draw_circle(circles,nodeRadius,circle_color);
                for (var i = 0; i < circles.length; i++) {
                    var beacon = circles[i];
                    //create Tooltips for beacons
                    ctx.fillStyle = tooltip_color;
                    ctx.fillText(beacon.tip, beacon.x - beacon.radius, beacon.y - beacon.radius);
                }
            }

            if(type == 'poi')
            {
                //draw poi connecting lines
                for (var i = 0; i < pois.length; i++) {
                    var poi = pois[i];
                    if(poi.connect != -1)
                    {
                        var line_start = poi;
                        var line_end = pois[poi.connect];
                    }
                    else
                    {
                        var line_start = pois[i];
                        var line_end = pois[i+1];
                    }

                    if(line_end != undefined)
                    {
                        draw_line(line_start.x-15,line_start.y-10,line_end.x-15,line_end.y-10,'orange');
                    }
                }

                //draw pois
                draw_circle(pois,poiRadius,poi_color);
                for (var i = 0; i < pois.length; i++) {
                    var beacon = pois[i];
                    //create Tooltips for beacons
                    ctx.fillStyle = tooltip_color;
                    ctx.fillText(beacon.tip, beacon.x - beacon.radius, beacon.y - beacon.radius);
                }
            }
        };

        var link = document.createElement('a');
        link.innerHTML = 'Download';
        link.addEventListener('click', function(ev) {
            link.href = myCanvas.toDataURL();
            link.download = type+".png";
            if(type == 'beacon' || type == 'calibrator' || type == 'gateway' || type == 'poi' )
            {
                excel_export(type);
            }

            //redraw all components
            draw_all();
            $('#EXPORTModal').modal('hide');
        }, false);

        $( "#exp_data" ).html('');
        $( "#exp_data" ).append(link);
        //Export data to Excel file

    }

    function excel_export(type)
    {
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $('#apply_loader').show();
        $.ajax({
            url: '{{url('events/floorplans/ajaxExport')}}',
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                type:type,
                floorplan_id:{{$id}},
            },
            dataType: 'json',
            success: function (response) {
                if (response.code == "200") {
                    window.open(response.path);
                    $('#apply_loader').hide();
                }
            }
        });
    }


    function save_nodes_path()
    {
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        console.log(beacons);
        $('#apply_loader').show();
        $.ajax({
            url: '{{url('events/floorplans/ajaxInsertNodesPaths')}}',
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                floorPlan:{{$id}},
                flooplan_height:{{$FloorplanData->height}},
                flooplan_width:{{$FloorplanData->width}},
                image_height:{{$ImageHeight}},
                image_width:{{$ImageWidth}},
                paths:relation,
                nodes:circles,
                beacons:beacons,
                calibrators:calibrators,
                pois:pois,
                gateways:gateways,
                beacon_delete:beacon_delete,
                node_delete:node_delete,
                calibrator_delete:calibrator_delete,
                poi_delete:poi_delete,
                gateway_delete:gateway_delete
            },
            dataType: 'json',
            success: function (response) {
                if (response.code == "200") {

                    //Update Nodes array after updating in database
                    circles = [];
                    jQuery.each(JSON.parse(response.nodes), function(index, item) {
                        circles.push({
                            x: parseInt(item.node_X),
                            y: parseInt(item.node_Y),
                            radius: nodeRadius,
                            tip:item.name,
                            id:item.id,
                            relation:parseInt(item.node_X)+parseInt(item.node_Y)
                        });
                        relation_index.push(item.id);
                    });

                    //Update beacons array after updating in database
                    beacons = [];
                    beacon_delete = [];
                    jQuery.each(JSON.parse(response.beacon), function(index, item) {
                        beacons.push({
                            x: parseInt(item.beacon_X),
                            y: parseInt(item.beacon_Y),
                            radius: beaconRadius,
                            tip:item.name,
                            id:item.id,
                            macid:item.macid,
                            temp: 0
                        });
                    });

                    //Update pois array after updating in database
                    pois = [];
                    jQuery.each(JSON.parse(response.poi), function(index, item) {
                        pois.push({
                            x: parseInt(item.poi_X),
                            y: parseInt(item.poi_Y),
                            radius: poiRadius,
                            tip:item.area,
                            area:item.area,
                            node:item.node_id,
                            id:item.id,
                            connect:item.connect
                        });
                    });


                    //Update calibrator array after updating in database
                    calibrators = [];
                    jQuery.each(JSON.parse(response.calibrator), function(index, item) {
                        calibrators.push({
                            x: parseInt(item.node_X),
                            y: parseInt(item.node_Y),
                            radius: calibratorRadius,
                            tip:item.name,
                            id:item.id,

                        });
                    });

                    //Update gateway array after updating in database
                    gateways = [];
                    jQuery.each(JSON.parse(response.gateway), function(index, item) {
                        gateways.push({
                            x: parseInt(item.gateway_X),
                            y: parseInt(item.gateway_Y),
                            radius: gatewayRadius,
                            tip:item.name,
                            id:item.id,

                        });
                        JSON.parse(response.gateway)
                    });

                    //Update all lines relations
                    relation = [];
                    node_delete = [];
                    jQuery.each(JSON.parse(response.relation), function(index, item) {
                        relation.push({
                            node:relation_index.indexOf(item.node_id),
                            rel_node:relation_index.indexOf(item.rel_node_id),
                            node_unique:item.node_unique,
                            rel_node_unique:item.rel_node_unique,
                            distance:item.distance
                        });
                    });
                    relation_index = [];
                    //stop all modes on save
                    start_drawing=0;
                    add_path=0;
                    add_calibrator=0;
                    add_poi=0;
                    add_gateway=0;
                    isDelete=0;
                    isDown=0;
                    isDrag=0;
                    drawBeaconRange=0;
                    drawCalibratorRange=0;
                    node_delete = [];
                    beacon_delete = [];
                    calibrator_delete = [];
                    poi_delete = [];
                    gateway_delete = [];
                    draw_all();
                    select_type('beacon');
                    $('#apply_loader').hide();
                }
            }
        })
    }

    function LoadPlanImage()
    {
        // Load Floorplan image on canvas
        var ctx = document.getElementById('canvas');
        if (ctx.getContext) {
            ctx = ctx.getContext('2d');
            //Loading of the floorplan image
            var img1 = new Image();
            img1.onload = function () {
                //draw background image
                ctx.drawImage(img1, 0, 0);
            };
            img1.src = '<?php echo url('/') . $FloorplanData->image; ?>';
        }
    }



    jQuery(document).ready(function ($) {

        //Load plan image on canvas
        LoadPlanImage();

        //Load default beacon tab and sub menu
        select_type('beacon');



        //Delete button code
        jQuery('#select_delete').click(function(){
            if(jQuery(this).hasClass('btn-primary'))
            {
                jQuery(this).removeClass('btn-primary');
                jQuery(this).addClass('btn-danger');
                jQuery(this).html('Delete');
            }
            else if(jQuery(this).hasClass('btn-danger'))
            {
                jQuery(this).removeClass('btn-danger');
                jQuery(this).addClass('btn-primary');
                save_nodes_path();
                jQuery(this).html('Select');
            }
        });

        //Sub menu buttons class change code
        jQuery('.btn-default').click(function(){
            jQuery('.btn-default').filter('.sub-button').each(function(i) {
                jQuery(this).removeClass('btn-primary');
            });
            jQuery(this).addClass('btn-primary');
        });
    });
</script>
{{ Html::script('/js/navigation.js') }}
<script type="text/javascript">
  var FloorBeacons     = '{!! $FloorBeacons !!}';
  var FloorNodes       = '{!! $FloorplanNodes !!}';
  var NodesRelation    = '{!! $AssignedNodes !!}';
  var FloorCalibrators = '{!! $FloorCalibrators !!}';
  var FloorPoi         = '{!! $FloorPois !!}';
  var FloorGateway     = '{!! $FloorGateway !!}';

  jQuery(document).ready(function () {

      //generate all beacons
      jQuery.each(JSON.parse(FloorBeacons), function(index, item) {
          beacons.push({
              x: parseInt(item.beacon_X),
              y: parseInt(item.beacon_Y),
              radius: beaconRadius,
              tip:item.name,
              id: item.id,
              macid: item.macid,
              temp: 0
          });
      });

      //generate all nodes
      jQuery.each(JSON.parse(FloorNodes), function(index, item) {
          circles.push({
              x: parseInt(item.node_X),
              y: parseInt(item.node_Y),
              radius: nodeRadius,
              tip:item.name,
              id:item.id,
              relation:item.relation
          });

          relation_index.push(item.id);

      });

      //generate all calibrators
      jQuery.each(JSON.parse(FloorCalibrators), function(index, item) {
          calibrators.push({
              x: parseInt(item.node_X),
              y: parseInt(item.node_Y),
              radius: calibratorRadius,
              tip:item.name,
              id:item.id,
          });
      });

      //Generate all pois
      jQuery.each(JSON.parse(FloorPoi), function(index, item) {
          pois.push({
              x: parseInt(item.poi_X),
              y: parseInt(item.poi_Y),
              radius: poiRadius,
              area:item.area,
              node:item.node_id,
              id:item.id,
              connect:item.connect
          });
      });


      //Generate all gateway
      jQuery.each(JSON.parse(FloorGateway), function(index, item) {
          gateways.push({
              x: parseInt(item.gateway_X),
              y: parseInt(item.gateway_Y),
              radius: gatewayRadius,
              tip:item.name,
              id:item.id,

          });
      });

      //generate all lines relations
      jQuery.each(JSON.parse(NodesRelation), function(index, item) {

        relation.push({
              node:relation_index.indexOf(item.node_id),
              rel_node:relation_index.indexOf(item.rel_node_id),
              node_unique:item.node_unique,
              rel_node_unique:item.rel_node_unique,
              distance:item.distance
          });
      });
      relation_index = [];
      //create all element
      draw_all()
  });
  function checkForValue(json, value) {
      for (key in json) {
          if (typeof (json[key]) === "object") {
              return checkForValue(json[key], value);
          } else if (json[key] === value) {
              return true;
          }
      }
      return false;
  }
</script>
@endsection