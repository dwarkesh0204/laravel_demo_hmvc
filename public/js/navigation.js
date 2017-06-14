var c            = document.getElementById("myCanvas");
var ctx          = c.getContext("2d");

var tapped       = false;
var canvas       = $("#myCanvas");
var canvasOffset = canvas.offset();
var offsetX      = canvasOffset.left;
var offsetY      = canvasOffset.top;
var scrollX      = canvas.scrollLeft();
var scrollY      = canvas.scrollTop();
function reOffset(){
    $('.navbar-main-menu').next('.navbar-static-top').offset(function(index, currentCoordinates) {
        /*console.log($(this).height());
        console.log(currentCoordinates.top);*/
        var BB = c.getBoundingClientRect();
        offsetX=BB.left;
        offsetY=BB.top;
    });
}
//window.onscroll=function(e){ reOffset(); }
window.onresize=function(e){ reOffset(); }

var start_drawing         = 0;
var circles               = [];
var beacons               = [];
var calibrators           = [];
var pois                  = [];
var gateways              = [];
var relation              = [];
var beacon_delete         = [];
var node_delete           = [];
var calibrator_delete     = [];
var poi_delete            = [];
var gateway_delete        = [];
var relation_temp         = -1;
var relation_temp_unique  = 0;

var nodeRadius            = 9;
var beaconRadius          = 6;
var deleteRadius          = 3;
var poiRadius             = 9;
var gatewayRadius         = 8;
var calibratorRadius      = 6;
var circle_color          = '#5F95FD';
var beacon_color          = '#FF5412';
var calibrator_color      = '#449D44';
var poi_color             = '#FF9C08';
var gateway_color         = '#A356D3';
var tooltip_color         = '#2E2F30';
var last_click_X          = 0;
var last_click_Y          = 0;
var first_single_click    = 1;
var cw                    = c.width;
var ch                    = c.height;
// Flag to indicate a drag is in process
// And the last XY position that has already been processed
var isDown                = false;
var isDrag                = false;
var lastX;
var lastY;
var draggingCircle        = -1;
var drawBeaconRange       = false;
var drawCalibratorRange   = false;
var BeaconRangeradius     = 328;
var CalibratorRangeradius = 328;
var isDelete              = false;
var relation_index        = [];

var node_hit              = -1;
var beacon_hit            = -1;
var calibrator_hit        = -1;
var poi_hit               = -1;
var gateway_hit           = -1;

var adjust_beacon         = false;
var adjust_node           = false;
var adjust_calibrator     = false;
var adjust_poi            = false;
var adjust_gateway        = false;

var add_calibrator        = false;
var add_path              = false;
var add_poi               = false;
var add_gateway           = false;
var poi_connected         = false;
var poi_draw_count        = 0;

var custom_position_x     = 0; //set according to the screen size
var custom_position_y     = 0; //set according to the screen size


$(document).ready(function(){
    $("#myCanvas").on("click",function(event){
        if(tapped)
        {
            clearTimeout(tapped);
            tapped = null;
            // do what needs to happen on double click.

            //if path drawing activated
            if(add_path == 1)
            {
                if(start_drawing == 0)
                {
                    // in case this becomes a connect start operation
                    lastX = parseInt(event.pageX - offsetX);
                    lastY = parseInt(event.pageY - offsetY);
                    // hit test all existing circles
                    var hit_connect = -1;
                    hit_connect = check_hit(circles,lastX,lastY);

                    if(hit_connect >= 0)
                    {
                        relation_temp        = hit_connect;
                        relation_temp_unique = circles[hit_connect].relation;
                    }
                    else
                    {
                        //First node on double click
                        circles.push({
                            x: event.pageX-offsetX,
                            y: event.pageY-offsetY,
                            radius: nodeRadius,
                            tip:'',
                            relation:event.pageX-offsetX+event.pageY-offsetY
                        });
                        circles[circles.length-1].tip = 'node-'+circles.length;

                        relation_temp        = circles.length-1;
                        relation_temp_unique = circles[circles.length-1].relation;
                        draw_all();

                    }
                    //Line Starting point on double click
                    start_drawing = 1;
                }
                else
                {
                    start_drawing = 0;
                }
            }
        }
        else
        {
            tapped = setTimeout(function(){
                // do what needs to happen on single click.

                //if Add calibrator mode is active
                if(add_calibrator == 1)
                {
                    //calibrator array
                    calibrators.push({
                        x: event.pageX-offsetX,
                        y: event.pageY-offsetY,
                        radius: calibratorRadius,
                        tip:'',
                    });

                    calibrators[calibrators.length-1].tip = 'Calibrator-'+calibrators.length;
                    draw_all();
                }

                //if Add gateway mode is active
                if(add_gateway == 1)
                {
                    //calibrator array
                    var AvailableGateway = ''
                    AvailableGateway = ajaxGetAvailableGateway();
                    if(AvailableGateway != '')
                    {
                        gateways.push({
                            x: event.pageX-offsetX,
                            y: event.pageY-offsetY,
                            radius: gatewayRadius,
                            tip:'',
                        });
                        gateways[gateways.length-1].tip = AvailableGateway;

                        if(checkForValue(gateways, AvailableGateway))
                        {
                            return false;
                        }
                        else
                        {
                            draw_all();
                        }
                    }
                }

                if(add_poi == 1)
                {
                    // in case this becomes a connect end operation
                    lastX = parseInt(event.pageX - offsetX);
                    lastY = parseInt(event.pageY - offsetY);
                    // hit test all existing circles
                    var poi_connect_end = -1;
                    poi_connect_end = check_hit(pois,lastX,lastY);

                    if(poi_connect_end == -1)
                    {
                        //poi array
                        pois.push({
                            x: event.pageX-offsetX,
                            y: event.pageY-offsetY,
                            radius: poiRadius,
                            area:'',
                            node:'',
                            connect:-1,
                        });
                        poi_draw_count++;
                    }
                    else
                    {
                        //open popup
                        pois[pois.length-1].connect = poi_connect_end;
                        jQuery('#POIModal').modal('show');
                    }
                    draw_all();
                }

                //if path drawing activated
                if(add_path == 1)
                {
                    if(start_drawing == 1)
                    {
                        // in case this becomes a connect end operation
                        lastX = parseInt(event.pageX - offsetX);
                        lastY = parseInt(event.pageY - offsetY);
                        // hit test all existing circles
                        var hit_connect_end = -1;
                        hit_connect_end = check_hit(circles,lastX,lastY);

                        //if not connect end operation
                        if(hit_connect_end == -1)
                        {
                            //node array
                            circles.push({
                                x: event.pageX-offsetX,
                                y: event.pageY-offsetY,
                                radius: nodeRadius,
                                tip:'',
                                relation:event.pageX-offsetX+event.pageY-offsetY
                            });
                            circles[circles.length-1].tip = 'node-'+circles.length;
                        }
                        else
                        {
                            //connect end operation - create relation
                            var a = circles[relation_temp].x - circles[hit_connect_end].x;
                            var b = circles[relation_temp].y - circles[hit_connect_end].y;
                            var distance = Math.sqrt( a*a + b*b );
                            relation.push({
                                node:relation_temp,
                                rel_node:hit_connect_end,
                                node_unique:relation_temp_unique,
                                rel_node_unique:circles[hit_connect_end].relation,
                                distance:distance
                            });
                            relation_temp=hit_connect_end;
                            relation_temp_unique = circles[hit_connect_end].relation;
                        }


                        //if not connect end operation - insert relation
                        if(relation_temp != -1 && hit_connect_end == -1)
                        {
                            var a = circles[relation_temp].x - circles[circles.length-1].x;
                            var b = circles[relation_temp].y - circles[circles.length-1].y;
                            var distance = Math.sqrt( a*a + b*b );
                            relation.push({
                                node:relation_temp,
                                rel_node:circles.length-1,
                                node_unique:relation_temp_unique,
                                rel_node_unique:circles[circles.length-1].relation,
                                distance:distance
                            });
                            relation_temp=circles.length-1;
                            relation_temp_unique = circles[circles.length-1].relation;
                        }
                        draw_all()
                    }
                }

                if(isDelete)
                {
                    //Check delete node selection tell the browser we'll handle this event
                    event.preventDefault();
                    event.stopPropagation();

                    // save the mouse position in case this becomes a drag operation
                    lastX = parseInt(event.pageX - offsetX);
                    lastY = parseInt(event.pageY - offsetY);

                    node_hit       = check_hit(circles,lastX,lastY);// hit test all existing nodes for delete
                    beacon_hit     = check_hit(beacons,lastX,lastY);// hit test all existing beacons for delete
                    calibrator_hit = check_hit(calibrators,lastX,lastY);// hit test all existing calibrators for delete
                    poi_hit        = check_hit(pois,lastX,lastY);// hit test all existing poi for delete
                    gateway_hit    = check_hit(gateways,lastX,lastY);// hit test all existing gateway for delete


                    if(beacon_hit >= 0)
                    {
                        //if beacon is selected for delete
                        draw_delete_circle(beacons[beacon_hit].x-custom_position_x,beacons[beacon_hit].y-custom_position_y)
                        beacon_delete.push({
                            x: beacons[beacon_hit].x,
                            y: beacons[beacon_hit].y,
                            radius: deleteRadius,
                            id:beacons[beacon_hit].id,
                            index:beacon_hit,
                            macid:beacons[beacon_hit].macid,
                        });
                    }

                    if(poi_hit >= 0)
                    {
                        //if beacon is selected for delete
                        draw_delete_circle(pois[poi_hit].x-custom_position_x,pois[poi_hit].y-custom_position_y)
                        poi_delete.push({
                            x: pois[poi_hit].x,
                            y: pois[poi_hit].y,
                            radius: deleteRadius,
                            id:pois[poi_hit].id,
                            index:poi_hit
                        });
                    }

                    //if node is selected for delete
                    if(node_hit >= 0)
                    {
                        draw_delete_circle(circles[node_hit].x-custom_position_x,circles[node_hit].y-custom_position_y)
                        node_delete.push({
                            x: circles[node_hit].x,
                            y: circles[node_hit].y,
                            radius: deleteRadius,
                            name:circles[node_hit].name,
                            id:circles[node_hit].id,
                            index:node_hit
                        });
                    }

                    //if calibrator is selected for delete
                    if(calibrator_hit >= 0)
                    {
                        //if calibrator is selected for delete
                        draw_delete_circle(calibrators[calibrator_hit].x-custom_position_x,calibrators[calibrator_hit].y-custom_position_y)
                        calibrator_delete.push({
                            x: calibrators[calibrator_hit].x,
                            y: calibrators[calibrator_hit].y,
                            radius: deleteRadius,
                            id:calibrators[calibrator_hit].id,
                            index:calibrator_hit
                        });
                    }

                    //if gateway is selected for delete
                    if(gateway_hit >= 0)
                    {
                        //if gateway is selected for delete
                        draw_delete_circle(gateways[gateway_hit].x-custom_position_x,gateways[gateway_hit].y-custom_position_y)
                        gateway_delete.push({
                            x: gateways[gateway_hit].x,
                            y: gateways[gateway_hit].y,
                            radius: deleteRadius,
                            id:gateways[gateway_hit].id,
                            index:gateway_hit,
                            tip:gateways[gateway_hit].tip,
                        });
                    }
                }
                tapped=null
            },300); //wait 300ms
        }
    });
});

function check_hit(target,MouseX,MouseY)
{
    var hit = -1;
    for (var i = 0; i < target.length; i++) {
        var circle = target[i];
        var dx     = MouseX - circle.x;
        var dy     = MouseY - circle.y;
        if (dx * dx + dy * dy < circle.radius * circle.radius) {
            hit = i;
        }
    }
    return hit;
}

function create_tooltip(target,mouseX,mouseY)
{
    for(var i=0;i<target.length;i++)
    {

        var h=target[i];
        var dx=mouseX-h.x;
        var dy=mouseY-h.y;
        if(dx*dx+dy*dy<h.radius*h.radius)
        {
            ctx.fillStyle = tooltip_color;
            ctx.fillText(h.tip,h.x-h.radius,h.y-h.radius);
        }
    }
}

function draw_all()
{
    ctx.clearRect(0, 0, cw, ch);
    //drawlines
    for (var i = 0; i < relation.length; i++) {
        var line_start = circles[relation[i].node];
        var line_end = circles[relation[i].rel_node];
        draw_line(line_start.x-custom_position_x,line_start.y-custom_position_y,line_end.x-custom_position_x,line_end.y-custom_position_y);
    }

    draw_circle(circles,nodeRadius,circle_color);//draw circles
    draw_circle(beacons,beaconRadius,beacon_color);//draw beacons
    draw_circle(calibrators,calibratorRadius,calibrator_color);//draw calibrator
    draw_circle(gateways,gatewayRadius,gateway_color);//draw gateway

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
            draw_line(line_start.x-custom_position_x,line_start.y-custom_position_y,line_end.x-custom_position_x,line_end.y-custom_position_y,'orange');
        }
    }

    //draw pois
    draw_circle(pois,poiRadius,poi_color);

    //draw beacon delete dot
    draw_circle(beacon_delete,deleteRadius,'#333333');
    //draw node delete dot
    draw_circle(node_delete,deleteRadius,'#333333');
    //draw calibrator delete dot
    draw_circle(calibrator_delete,deleteRadius,'#333333');
    //draw poi delete dot
    draw_circle(poi_delete,deleteRadius,'#333333');
    //draw gateway delete dot
    draw_circle(gateway_delete,deleteRadius,'#333333');
}

//Draw Circle
function draw_circle(target,radius,colour)
{
    for (var i = 0; i < target.length; i++)
    {
        var circle = target[i];
        ctx.beginPath();
        ctx.arc(circle.x-custom_position_x,circle.y-custom_position_y,radius,0,2*Math.PI);
        ctx.closePath();
        ctx.fillStyle = colour;
        ctx.fill();
    }
}

//Draw Line
function draw_line(startX,startY,endX,endY,color = 'red')
{
    ctx.beginPath();
    ctx.lineWidth = 3;
    ctx.lineJoin = 'round';
    ctx.lineCap = 'round';
    ctx.strokeStyle = color;
    ctx.moveTo(startX,startY);
    ctx.lineTo(endX,endY);
    ctx.closePath();
    ctx.stroke();
}

function draw_delete_circle(X,Y,radius=deleteRadius)
{
    ctx.beginPath();
    ctx.arc(X,Y,radius,0,2*Math.PI);
    ctx.closePath();
    ctx.fillStyle = '#333333';
    ctx.fill();
}

function handleMouseDown(e) {

    if(isDrag)
    {
        // tell the browser we'll handle this event
        e.preventDefault();
        e.stopPropagation();

        // save the mouse position in case this becomes a drag operation
        lastX = parseInt(e.pageX - offsetX);
        lastY = parseInt(e.pageY - offsetY);

        if(adjust_node)//check nodes for drag select
        {
            node_hit = check_hit(circles,lastX,lastY);// hit test all existing nodes
        }

        if(adjust_beacon)//check beacons for drag select
        {
            beacon_hit = check_hit(beacons,lastX,lastY);// hit test all existing beacons
        }

        if(adjust_calibrator)//check calibrator for drag select
        {
            calibrator_hit = check_hit(calibrators,lastX,lastY);// hit test all existing calibrators
        }

        if(adjust_poi)//check pois for drag select
        {
            poi_hit = check_hit(pois,lastX,lastY);// hit test all existing pois
        }

        if(adjust_gateway)//check gateway for drag select
        {
            gateway_hit = check_hit(gateways,lastX,lastY);// hit test all existing gateways
        }


        // if hit then set the isDown flag to start a drag
        if (node_hit >= 0 || beacon_hit >= 0 || calibrator_hit  >= 0 || poi_hit >= 0 || gateway_hit >= 0) {

            //Node drag hit
            if(node_hit >= 0){ draggingCircle = circles[node_hit]; }

            //Beacon drag hit
            if(beacon_hit >= 0){ draggingCircle = beacons[beacon_hit]; }

            //Calibrator drag hit
            if(calibrator_hit >= 0){ draggingCircle = calibrators[calibrator_hit]; }

            //POI drag hit
            if(poi_hit >= 0){ draggingCircle = pois[poi_hit]; }

            //Gateway drag hit
            if(gateway_hit >= 0)
            { draggingCircle = gateways[gateway_hit]; }

            isDown = true;
        }
        else
        {
            /*isDown = false;
            isDrag = false;
            selected_circle_index = null;*/
        }
    }
}

function handleMouseUp(e) {
    // tell the browser we'll handle this event
    e.preventDefault();
    e.stopPropagation();

    // stop the drag
    isDown = false;
    //isDrag = false;
    node_hit = -1;
    beacon_hit = -1;
    calibrator_hit = -1;
    poi_hit = -1;
    gateway_hit = -1;
    draggingCircle = null;

    //Live X Y Positions
    jQuery('#live_x').html('');
    jQuery('#live_y').html('');
}

function handleMouseMove(e) {

    if(!isDrag && !isDown && !start_drawing)//Not dragging and not drawing
    {
        // tell the browser we'll handle this event
        e.preventDefault();
        e.stopPropagation();
        mouseX=parseInt(e.pageX-offsetX);
        mouseY=parseInt(e.pageY-offsetY);

        //tooltips for the node elements
        draw_all();

        create_tooltip(circles,mouseX,mouseY);//create Tooltips for nodes
        create_tooltip(beacons,mouseX,mouseY);//create Tooltips for beacons
        create_tooltip(pois,mouseX,mouseY);//create Tooltips for pois
        create_tooltip(calibrators,mouseX,mouseY);//create Tooltips for calibrators
        create_tooltip(gateways,mouseX,mouseY);//create Tooltips for gateways
    }
    else if(isDrag && isDown && !start_drawing) //if we are dragging
    {

        // tell the browser we'll handle this event
        e.preventDefault();
        e.stopPropagation();

        // get the current mouse position
        mouseX = parseInt(e.pageX - offsetX);
        mouseY = parseInt(e.pageY - offsetY);

        //Live X Y Positions
        jQuery('#live_x').html(mouseX);
        jQuery('#live_y').html(mouseY);

        // calculate how far the mouse has moved since the last mousemove event was processed
        var dx = mouseX - lastX;
        var dy = mouseY - lastY;

        // reset the lastX/Y to the current mouse position
        lastX = mouseX;
        lastY = mouseY;

        // change the target circles position by the distance the mouse has moved since the last mousemove event
        draggingCircle.x += dx;
        draggingCircle.y += dy;

        draw_all();// redraw all the circles & lines
    }
}
// listen for mouse events
$("#myCanvas").mousedown(function (e) {
    handleMouseDown(e);
});
$("#myCanvas").mousemove(function (e) {
    handleMouseMove(e);
});
$("#myCanvas").mouseup(function (e) {
    handleMouseUp(e);
});
$("#myCanvas").mouseout(function (e) {
    handleMouseUp(e);
});