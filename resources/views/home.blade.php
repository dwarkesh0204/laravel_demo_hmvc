@extends('layouts.app')
@section('content')
    <link rel="stylesheet" href="{{ asset('css/graph_style.css') }}" />
    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif
    <div class="container">
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>
                <div class="panel-body">
                    <div class="col-md-12">
                        <div id="main-sortable-area">
                            <?php
                            if (count($widgets) > 0) {
                            for ($i=0; $i < count($widgets); $i++) {
                            if ($widgets[$i]->name == 'pc_0') { ?>
                            <div class="portlet-container col-sm-12" id="pc_0">
                                <div class="portlet">
                                    <div class="portlet-content">
                                    </div>
                                </div>
                            </div>
                            <?php } else if ($widgets[$i]->name == 'pc_1') { ?>
                            <div class="portlet-container col-sm-3 visitor_clusters" id="pc_1">
                                <div class="portlet">
                                    <div class="portlet-header">Visitor Clusters</div>
                                    <div class="portlet-content">
                                    </div>
                                </div>
                            </div>
                            <?php } else if ($widgets[$i]->name == 'pc_2') { ?>
                            <div class="portlet-container col-sm-3 avg_dwell_time" id="pc_2">
                                <div class="portlet">
                                    <div class="portlet-content">
                                        <div class="content_bx_1">
                                            <div class="lbl">AVG. DWELL TIME</div>
                                            <div class="text">4.5 <span>hrs</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } else if ($widgets[$i]->name == 'pc_3') { ?>
                            <div class="portlet-container col-sm-3 loyalty_map" id="pc_3">
                                <div class="portlet">
                                    <div class="portlet-content">
                                        <div class="content_bx_3">
                                            <div class="lbl">Loyalty Map</div>
                                            <div class="text">Loyal - 23 <br/> Advocates - 22 <br/> Apostles - 15</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } else if ($widgets[$i]->name == 'pc_4') { ?>
                            <div class="portlet-container col-sm-3 visit_to_buy" id="pc_4">
                                <div class="portlet">
                                    <div class="portlet-content">
                                        <div class="content_bx">
                                            <div class="lbl">Visit to Buy Ratio</div>
                                            <div class="text">4</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } else if ($widgets[$i]->name == 'pc_5') { ?>
                            <div class="portlet-container col-sm-3 most_engaging_section" id="pc_5">
                                <div class="portlet">
                                    <div class="portlet-content">
                                        <div class="content_bx">
                                            <div class="lbl">Most Engaging Section</div>
                                            <div class="text">Womens Fashion</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } else if ($widgets[$i]->name == 'pc_6') { ?>
                            <div class="portlet-container col-sm-3 notification_triggers" id="pc_6">
                                <div class="portlet">
                                    <div class="portlet-content">
                                        <div class="content_bx">
                                            <div class="lbl">Notification Triggers</div>
                                            <div class="text">150</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } else if ($widgets[$i]->name == 'pc_7') { ?>
                            <div class="portlet-container col-sm-12 compare_store_visitors" id="pc_7">
                                <div class="portlet">
                                    <div class="portlet-header">Compare Store Visitors</div>
                                    <div class="portlet-content">
                                    </div>
                                </div>
                            </div>
                            <?php }
                            }
                            }?>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/datepicker/jquery-ui.js') }}"></script>
    <script src="{{ asset('js/jquery.ui.touch-punch.min.js') }}"></script>
    <script src="{{ asset('js/graph_script.js') }}"></script>
    <script src="{{ asset('js/heatmap/heatmap.js') }}"></script>
    <script src="{{ asset('js/heatmap-data.js') }}"></script>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {

            <?php
            //Below shoud be replaced with dynamic data for the graph
            $x_axis = $y_axis = [];
            $x_axis = array('12 AM', '1 AM', '2 AM', '3 AM', '4 AM', '5 AM', '6 AM', '7 AM', '8 AM', '9 AM', '10 AM', '11 AM', '12 PM', '1 PM', '2 PM', '3 PM', '4 PM', '5 PM', '6 PM', '7 PM', '8 PM', '9 PM', '10 PM', '11 PM');
            $y_axis[] = array(
                'name' => 'Repeat',
                'data' => array(7, 6, 9, 14, 18, 21, 25, 26, 23, 18, 13, 9, 7, 6, 9, 14, 18, 21, 25, 26, 23, 18, 13, 9)
            );
            $y_axis[] = array('name' => 'First Time', 'data' => array(3, 4, 5, 8, 11, 15, 17, 16, 14, 10, 6, 4, 13, 9, 7, 6, 9, 14, 18, 21, 25, 26, 23, 24));
            ?>

            $('#pc_0 .portlet-content').chart_line({
                        title: 'All Visitors Overview',
                        x_axis: '<?php echo json_encode($x_axis);?>',
                        y_axis: '<?php echo json_encode($y_axis);?>'
                    });
            <?php
            $data = [];
            $data[] = array('name' => 'Repeat', 'y' => 302);
            $data[] = array('name' => 'First Time', 'y' => 208);
            ?>
            $('#pc_1 .portlet-content').chart_pie({
                        data : '<?php echo json_encode($data);?>'
                    });

            <?php
            //Below shoud be replaced with dynamic data for the graph
            $x_axis = $y_axis = [];
            $x_axis = array('Store 1', 'Store 2', 'Store 3', 'Store 4', 'Store 5', 'Store 6', 'Store 7');
            $y_axis = array(107, 133, 1052, 100, 205, 999, 515);
            ?>

            $('#pc_7 .portlet-content').chart_bar({
                        title: 'All Visitors Overview',
                        x_axis: '<?php echo json_encode($x_axis);?>',
                        y_axis: '<?php echo json_encode($y_axis);?>'
                    });

            $('#main-sortable-area').drag_n_drop({
                handle_selector: '.portlet',
                placeholder_class: 'portlet-placeholder',
                id_selector: '.portlet-container',
                onDropStop: function(data) { //sortable on stop callback
                    var ordering_data = data.ordering_data;
                    $.ajax({
                        type:'POST',
                        url:'{{url('/updateWidgetOrdering')}}',
                        data:{'_token': '<?php echo csrf_token() ?>', 'ordering_data' : ordering_data}
                    });
                }
            });

        });
    </script>
@endsection
