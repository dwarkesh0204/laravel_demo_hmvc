var c=document.getElementById("myCanvas");
var ctx=c.getContext("2d");

var tapped=false;
var canvas = $("#myCanvas");
var canvasOffset = canvas.offset();
var offsetX = canvasOffset.left;
var offsetY = canvasOffset.top;
var scrollX = canvas.scrollLeft();
var scrollY = canvas.scrollTop();
function reOffset(){
    var BB=c.getBoundingClientRect();
    offsetX=BB.left;
    offsetY=BB.top;
}
reOffset();
//window.onscroll=function(e){ reOffset(); }
window.onresize=function(e){ reOffset(); }

var start_drawing = 0;
var circles = [];
var beacons = [];
var calibrators = [];
var pois = [];
var gateways = [];
var relation = [];
var beacon_delete = [];
var node_delete = [];
var calibrator_delete = [];
var poi_delete = [];
var gateway_delete = [];
var relation_temp = -1;
var relation_temp_unique = 0;

var nodeRadius = 9;
var beaconRadius = 6;
var deleteRadius = 3;
var poiRadius = 9;
var gatewayRadius = 8;
var calibratorRadius = 6;
var circle_color = '#5F95FD';
var beacon_color  = '#FF5412';
var calibrator_color  = '#449D44';
var poi_color = '#FF9C08';
var gateway_color = '#A356D3';
var tooltip_color = '#2E2F30';
var last_click_X = 0;
var last_click_Y = 0;
var first_single_click = 1;
var cw = c.width;
var ch = c.height;
// Flag to indicate a drag is in process
// And the last XY position that has already been processed
var isDown = false;
var isDrag = false;
var lastX;
var lastY;
var draggingCircle = -1;
var drawBeaconRange = false;
var drawCalibratorRange = false;
var BeaconRangeradius = 328;
var CalibratorRangeradius = 328;
var isDelete = false;
var relation_index = [];

var node_hit = -1;
var beacon_hit = -1;
var calibrator_hit = -1;
var poi_hit = -1;
var gateway_hit = -1;

var adjust_beacon = false;
var adjust_node = false;
var adjust_calibrator = false;
var adjust_poi = false;
var adjust_gateway = false;

var add_calibrator = false;
var add_path = false;
var add_poi = false;
var add_gateway = false;
var poi_connected = false;
var poi_draw_count = 0;


$(document).ready(function(){
    $("#myCanvas").on("click",function(event){
        if(tapped){
            clearTimeout(tapped);
            tapped=null;
            // do what needs to happen on double click.

            //if path drawing activated
            if(add_path==1)
            {
                if(start_drawing == 0)
                {
                    // in case this becomes a connect start operation
                    lastX = parseInt(event.pageX - offsetX);
                    lastY = parseInt(event.pageY - offsetY);
                    // hit test all existing circles
                    var hit_connect = -1;
                    for (var i = 0; i < circles.length; i++) {
                        var circle = circles[i];
                        var dx = lastX - circle.x;
                        var dy = lastY - circle.y;
                        if (dx * dx + dy * dy < circle.radius * circle.radius) {
                            hit_connect = i;
                        }
                    }

                    if(hit_connect >= 0)
                    {
                        relation_temp = hit_connect;
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

                        relation_temp = circles.length-1;
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


        } else {
            tapped=setTimeout(function(){
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
                    gateways.push({
                        x: event.pageX-offsetX,
                        y: event.pageY-offsetY,
                        radius: gatewayRadius,
                        tip:'',
                    });

                    gateways[gateways.length-1].tip = 'Gateway-'+gateways.length;
                    draw_all();
                }

                if(add_poi == 1)
                {
                    // in case this becomes a connect end operation
                    lastX = parseInt(event.pageX - offsetX);
                    lastY = parseInt(event.pageY - offsetY);
                    // hit test all existing circles
                    var poi_connect_end = -1;
                    for (var i = 0; i < pois.length; i++) {
                        var poi = pois[i];
                        var dx = lastX - poi.x;
                        var dy = lastY - poi.y;
                        if (dx * dx + dy * dy < poi.radius * poi.radius) {
                            poi_connect_end = i;
                        }
                    }

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
                if(add_path==1)
                {
                    if(start_drawing == 1)
                    {
                        // in case this becomes a connect end operation
                        lastX = parseInt(event.pageX - offsetX);
                        lastY = parseInt(event.pageY - offsetY);
                        // hit test all existing circles
                        var hit_connect_end = -1;
                        for (var i = 0; i < circles.length; i++) {
                            var circle = circles[i];
                            var dx = lastX - circle.x;
                            var dy = lastY - circle.y;
                            if (dx * dx + dy * dy < circle.radius * circle.radius) {
                                hit_connect_end = i;
                            }
                        }

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
                    //Check delete node selection
                    // tell the browser we'll handle this event
                    event.preventDefault();
                    event.stopPropagation();

                    // save the mouse position
                    // in case this becomes a drag operation
                    lastX = parseInt(event.pageX - offsetX);
                    lastY = parseInt(event.pageY - offsetY);

                    // hit test all existing nodes for delete
                    for (var i = 0; i < circles.length; i++) {
                        var circle = circles[i];
                        var dx = lastX - circle.x;
                        var dy = lastY - circle.y;
                        if (dx * dx + dy * dy < circle.radius * circle.radius) {
                            node_hit = i;
                        }
                    }

                    // hit test all existing beacons for delete
                    for (var b = 0; b < beacons.length; b++) {
                        var beacon = beacons[b];
                        var dx = lastX - beacon.x;
                        var dy = lastY - beacon.y;
                        if (dx * dx + dy * dy < beacon.radius * beacon.radius) {
                            beacon_hit = b;
                        }
                    }

                    // hit test all existing calibrators for delete
                    for (var b = 0; b < calibrators.length; b++) {
                        var calibrator = calibrators[b];
                        var dx = lastX - calibrator.x;
                        var dy = lastY - calibrator.y;
                        if (dx * dx + dy * dy < calibrator.radius * calibrator.radius) {
                            calibrator_hit = b;
                        }
                    }

                    // hit test all existing poi for delete
                    for (var b = 0; b < pois.length; b++) {
                        var poi = pois[b];
                        var dx = lastX - poi.x;
                        var dy = lastY - poi.y;
                        if (dx * dx + dy * dy < poi.radius * poi.radius) {
                            poi_hit = b;
                        }
                    }

                    // hit test all existing gateway for delete
                    for (var b = 0; b < gateways.length; b++) {
                        var gateway = gateways[b];
                        var dx = lastX - gateway.x;
                        var dy = lastY - gateway.y;
                        if (dx * dx + dy * dy < gateway.radius * gateway.radius) {
                            gateway_hit = b;
                        }
                    }

                    if(beacon_hit >=0)
                    {
                        //if beacon is selected for delete
                        draw_delete_circle(beacons[beacon_hit].x-15,beacons[beacon_hit].y-10)
                        beacon_delete.push({
                            x: beacons[beacon_hit].x,
                            y: beacons[beacon_hit].y,
                            radius: deleteRadius,
                            id:beacons[beacon_hit].id,
                            index:beacon_hit
                        });
                    }

                    if(poi_hit >=0)
                    {
                        //if beacon is selected for delete
                        draw_delete_circle(pois[poi_hit].x-15,pois[poi_hit].y-10)
                        poi_delete.push({
                            x: pois[poi_hit].x,
                            y: pois[poi_hit].y,
                            radius: deleteRadius,
                            id:pois[poi_hit].id,
                            index:poi_hit
                        });
                    }

                    //if node is selected for delete
                    if(node_hit >=0)
                    {
                        draw_delete_circle(circles[node_hit].x-15,circles[node_hit].y-10)
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
                    if(calibrator_hit >=0)
                    {
                        //if calibrator is selected for delete
                        draw_delete_circle(calibrators[calibrator_hit].x-15,calibrators[calibrator_hit].y-10)
                        calibrator_delete.push({
                            x: calibrators[calibrator_hit].x,
                            y: calibrators[calibrator_hit].y,
                            radius: deleteRadius,
                            id:calibrators[calibrator_hit].id,
                            index:calibrator_hit
                        });
                    }

                    //if gateway is selected for delete
                    if(gateway_hit >=0)
                    {
                        //if gateway is selected for delete
                        draw_delete_circle(gateways[gateway_hit].x-15,gateways[gateway_hit].y-10)
                        gateway_delete.push({
                            x: gateways[gateway_hit].x,
                            y: gateways[gateway_hit].y,
                            radius: deleteRadius,
                            id:gateways[gateway_hit].id,
                            index:gateway_hit
                        });
                    }
                }
                tapped=null
            },300); //wait 300ms
        }
    });
});


function draw_all()
{
    ctx.clearRect(0, 0, cw, ch);
    //drawlines
    for (var i = 0; i < relation.length; i++) {
        var line_start = circles[relation[i].node];
        var line_end = circles[relation[i].rel_node];
        draw_line(line_start.x-15,line_start.y-10,line_end.x-15,line_end.y-10);
    }

    //draw circles
    for (var i = 0; i < circles.length; i++) {
        var circle = circles[i];
        draw_circle(circle.x-15,circle.y-10);
    }

    //draw beacons
    for (var i = 0; i < beacons.length; i++){
        var beacon = beacons[i];
        draw_beacon(beacon.x-15,beacon.y-10);
    }

    //draw calibrator
    for (var i = 0; i < calibrators.length; i++){
        var calibrator = calibrators[i];
        draw_calibrator(calibrator.x-15,calibrator.y-10);
    }

    //draw gateway
    for (var i = 0; i < gateways.length; i++){
        var gateway = gateways[i];
        draw_gateway(gateway.x-15,gateway.y-10);
    }

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
    for (var i = 0; i < pois.length; i++) {
        var poi = pois[i];
        draw_poi(poi.x-15,poi.y-10);
    }

    //draw beacon delete dot
    for (var i=0;i < beacon_delete.length; i++)
    {
        var beacondelete = beacon_delete[i];
        draw_delete_circle(beacondelete.x-15,beacondelete.y-10,deleteRadius);
    }

    //draw node delete dot
    for (var i=0;i < node_delete.length; i++)
    {
        var nodedelete = node_delete[i];
        draw_delete_circle(nodedelete.x-15,nodedelete.y-10,deleteRadius);
    }

    //draw calibrator delete dot
    for (var i=0;i < calibrator_delete.length; i++)
    {
        var calibratordelete = calibrator_delete[i];
        draw_delete_circle(calibratordelete.x-15,calibratordelete.y-10,deleteRadius);
    }

    //draw poi delete dot
    for (var i=0;i < poi_delete.length; i++)
    {
        var poidelete = poi_delete[i];
        draw_delete_circle(poidelete.x-15,poidelete.y-10,deleteRadius);
    }

    //draw gateway delete dot
    for (var i=0;i < gateway_delete.length; i++)
    {
        var gatewaydelete = gateway_delete[i];
        draw_delete_circle(gatewaydelete.x-15,gatewaydelete.y-10,deleteRadius);
    }
}

//Draw Circle
function draw_circle(X,Y,radius=nodeRadius)
{
    ctx.beginPath();
    ctx.arc(X,Y,radius,0,2*Math.PI);
    ctx.closePath();
    ctx.fillStyle = circle_color;
    ctx.fill();
}

//Draw Pois
function draw_poi(X,Y,radius=poiRadius)
{
    ctx.beginPath();
    ctx.arc(X,Y,radius,0,2*Math.PI);
    ctx.closePath();
    ctx.fillStyle = poi_color;
    ctx.fill();
}

//Draw Beacon
function draw_beacon(X,Y,radius=beaconRadius)
{
    ctx.beginPath();
    ctx.arc(X,Y,radius,0,2*Math.PI);
    ctx.closePath();
    ctx.fillStyle = beacon_color;
    ctx.fill();
}

//Draw Calibrator
function draw_calibrator(X,Y,radius=calibratorRadius)
{
    ctx.beginPath();
    ctx.arc(X,Y,radius,0,2*Math.PI);
    ctx.closePath();
    ctx.fillStyle = calibrator_color;
    ctx.fill();
}

//Draw Gateway
function draw_gateway(X,Y,radius=gatewayRadius)
{
    ctx.beginPath();
    ctx.arc(X,Y,radius,0,2*Math.PI);
    ctx.closePath();
    ctx.fillStyle = gateway_color;
    ctx.fill();
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

        // save the mouse position
        // in case this becomes a drag operation
        lastX = parseInt(e.pageX - offsetX);
        lastY = parseInt(e.pageY - offsetY);

        //check nodes for drag select
        if(adjust_node)
        {
            // hit test all existing nodes
            for (var i = 0; i < circles.length; i++) {
                var circle = circles[i];
                var dx = lastX - circle.x;
                var dy = lastY - circle.y;
                if (dx * dx + dy * dy < circle.radius * circle.radius) {
                    node_hit = i;
                }
            }
        }

        //check beacons for drag select
        if(adjust_beacon)
        {
            // hit test all existing beacons
            for (var b = 0; b < beacons.length; b++) {
                var beacon = beacons[b];
                var dx = lastX - beacon.x;
                var dy = lastY - beacon.y;
                if (dx * dx + dy * dy < beacon.radius * beacon.radius) {
                    beacon_hit = b;
                }
            }
        }

        //check calibrator for drag select
        if(adjust_calibrator)
        {
            // hit test all existing calibrators
            for (var c = 0; c < calibrators.length; c++) {
                var calibrator = calibrators[c];
                var dx = lastX - calibrator.x;
                var dy = lastY - calibrator.y;
                if (dx * dx + dy * dy < calibrator.radius * calibrator.radius) {
                    calibrator_hit = c;
                }
            }
        }

        //check pois for drag select
        if(adjust_poi)
        {
            // hit test all existing pois
            for (var p = 0; p < pois.length; p++) {
                var poi = pois[p];
                var dx = lastX - poi.x;
                var dy = lastY - poi.y;
                if (dx * dx + dy * dy < poi.radius * poi.radius) {
                    poi_hit = p;
                }
            }
        }

        //check gateway for drag select
        if(adjust_gateway)
        {
            // hit test all existing gateways
            for (var p = 0; p < gateways.length; p++) {
                var gateway = gateways[p];
                var dx = lastX - gateway.x;
                var dy = lastY - gateway.y;
                if (dx * dx + dy * dy < gateway.radius * gateway.radius) {
                    gateway_hit = p;
                }
            }
        }




        // if hit then set the isDown flag to start a drag
        if (node_hit >= 0 || beacon_hit >= 0 || calibrator_hit  >= 0 || poi_hit >= 0 || gateway_hit >= 0) {

            //Node drag hit
            if(node_hit >= 0)
            {
                draggingCircle = circles[node_hit];
            }

            //Beacon drag hit
            if(beacon_hit >= 0)
            {
                draggingCircle = beacons[beacon_hit];
            }

            //Calibrator drag hit
            if(calibrator_hit >= 0)
            {
                draggingCircle = calibrators[calibrator_hit];
            }

            //POI drag hit
            if(poi_hit >= 0)
            {
                draggingCircle = pois[poi_hit];
            }

            //Gateway drag hit
            if(gateway_hit >= 0)
            {
                draggingCircle = gateways[gateway_hit];
            }

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

        //create Tooltips for nodes
        for(var i=0;i<circles.length;i++)
        {
            var h=circles[i];
            var dx=mouseX-h.x;
            var dy=mouseY-h.y;
            if(dx*dx+dy*dy<h.radius*h.radius){
                ctx.fillStyle = tooltip_color;
                ctx.fillText(h.tip,h.x-h.radius,h.y-h.radius);
            }
        }

        //create Tooltips for beacons
        for(var i=0;i<beacons.length;i++)
        {
            var h=beacons[i];
            var dx=mouseX-h.x;
            var dy=mouseY-h.y;
            if(dx*dx+dy*dy<h.radius*h.radius){
                ctx.fillStyle = tooltip_color;
                ctx.fillText(h.tip,h.x-h.radius,h.y-h.radius);
            }
        }

        //create Tooltips for pois
        for(var i=0;i<pois.length;i++)
        {
            var h=pois[i];
            var dx=mouseX-h.x;
            var dy=mouseY-h.y;
            if(dx*dx+dy*dy<h.radius*h.radius){
                ctx.fillStyle = tooltip_color;
                ctx.fillText(h.area,h.x-h.radius,h.y-h.radius);
            }
        }

        //create Tooltips for calibrators
        for(var i=0;i<calibrators.length;i++)
        {
            var h=calibrators[i];
            var dx=mouseX-h.x;
            var dy=mouseY-h.y;
            if(dx*dx+dy*dy<h.radius*h.radius){
                ctx.fillStyle = tooltip_color;
                ctx.fillText(h.tip,h.x-h.radius,h.y-h.radius);
            }
        }

        //create Tooltips for gateways
        for(var i=0;i<gateways.length;i++)
        {
            var h=gateways[i];
            var dx=mouseX-h.x;
            var dy=mouseY-h.y;
            if(dx*dx+dy*dy<h.radius*h.radius){
                ctx.fillStyle = tooltip_color;
                ctx.fillText(h.tip,h.x-h.radius,h.y-h.radius);
            }
        }
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

        // calculate how far the mouse has moved
        // since the last mousemove event was processed
        var dx = mouseX - lastX;
        var dy = mouseY - lastY;

        // reset the lastX/Y to the current mouse position
        lastX = mouseX;
        lastY = mouseY;

        // change the target circles position by the
        // distance the mouse has moved since the last
        // mousemove event
        draggingCircle.x += dx;
        draggingCircle.y += dy;

        // redraw all the circles & lines
        draw_all();
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