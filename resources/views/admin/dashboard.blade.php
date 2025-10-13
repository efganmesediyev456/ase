@extends(config('saysay.crud.layout'))

@section('content')
    <style>
        .chart{
            height: 500px;
            width: 100%;
        }
        #chart_link {
            height: 475px;
        }
        .card {
            position: relative;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-direction: column;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border: 1px solid rgba(0,0,0,.125);
            border-radius: .1875rem;
        }
        .card {
            margin-bottom: 1.25rem;
            box-shadow: 0 1px 2px rgba(0,0,0,.05);
        }
        .card-body {
            -ms-flex: 1 1 auto;
            flex: 1 1 auto;
            padding: 1.25rem;
        }
    </style>


    @permission('read-dashboard')
        <div class="row">
            <div class="col-lg-4">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card card-body">
                            <div class="card-header header-elements-inline">
                                <h4 class="card-title">Total</h4>
                            </div>
                            <div class="row text-center">
                                <div class="col-lg-4">
                                    <p><i class="icon-users2 icon-2x d-inline-block text-info"></i></p>
                                    <h5 class="font-weight-semibold mb-0">{{ number_format($totalUsers) }}</h5>
                                    <span class="text-muted font-size-sm">Users</span>
                                </div>

                                <div class="col-lg-4">
                                    <p><i class="icon-point-up icon-2x d-inline-block text-warning"></i></p>
                                    <h5 class="font-weight-semibold mb-0">{{ number_format($totalOrders) }}</h5>
                                    <span class="text-muted font-size-sm">Orders</span>
                                </div>

                                <div class="col-lg-4">
                                    <p><i class="icon-stack3 icon-2x d-inline-block text-success"></i></p>
                                    <h5 class="font-weight-semibold mb-0">{{ number_format($totalPackages) }}</h5>
                                    <span class="text-muted font-size-sm">Packages</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="card card-body">
                            <div class="card-header header-elements-inline">
                                <h4 class="card-title">Cashback</h4>
                            </div>
                            <div class="row text-center">
                                <div class="col-lg-4">
                                    <p><i class="icon-stack-check icon-2x d-inline-block text-info"></i></p>
                                    <h5 class="font-weight-semibold mb-0">{{ ($balance['active']['azn'])??'' }}</h5>
                                    <span class="text-muted font-size-sm">Active</span>
                                </div>

                                <div class="col-lg-4">
                                    <p><i class="icon-stack-minus icon-2x d-inline-block text-warning"></i></p>
                                    <h5 class="font-weight-semibold mb-0">{{ ($balance['pending']['azn'])??'' }}</h5>
                                    <span class="text-muted font-size-sm">Pending</span>
                                </div>

                                <div class="col-lg-4">
                                    <p><i class="icon-stack-plus icon-2x d-inline-block text-success"></i></p>
                                    <h5 class="font-weight-semibold mb-0">{{ ($balance['total']['azn'])??'' }}</h5>
                                    <span class="text-muted font-size-sm">Total</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="card card-body">
                            <div class="card-header header-elements-inline">
                                <h4 class="card-title">The last 7 days</h4>
                            </div>
                            <div class="row text-center">
                                <div class="col-lg-4">
                                    <p><i class="icon-users2 icon-2x d-inline-block text-info"></i></p>
                                    <h5 class="font-weight-semibold mb-0">{{ number_format($total7Users) }}</h5>
                                    <span class="text-muted font-size-sm">Users</span>
                                </div>

                                <div class="col-lg-4">
                                    <p><i class="icon-point-up icon-2x d-inline-block text-warning"></i></p>
                                    <h5 class="font-weight-semibold mb-0">{{ number_format($total7Orders) }}</h5>
                                    <span class="text-muted font-size-sm">Orders</span>
                                </div>

                                <div class="col-lg-4">
                                    <p><i class="icon-stack3 icon-2x d-inline-block text-success"></i></p>
                                    <h5 class="font-weight-semibold mb-0">{{ number_format($total7Packages) }}</h5>
                                    <span class="text-muted font-size-sm">Packages</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <!-- Basic bar chart -->
                <div class="card">
                    <div class="card-body">
                        <div class="card-header header-elements-inline">
                            <h5 class="card-title">Links</h5>
                        </div>

                        <div class="chart-container">
                            <div class="chart" id="chart_link"></div>
                        </div>
                    </div>
                </div>
                <!-- /basic bar chart -->
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <!-- Basic bar chart -->
                <div class="card">
                    <div class="card-body">
                        <div class="card-header header-elements-inline">
                            <h5 class="card-title">Users package activity</h5>
                        </div>

                        <div class="chart-container">
                            <div class="chart" id="chart_user"></div>
                        </div>
                    </div>
                </div>
                <!-- /basic bar chart -->
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <!-- Basic bar chart -->
                <div class="card">
                    <div class="card-body">
                        <div class="card-header header-elements-inline">
                            <h5 class="card-title">Package activity</h5>
                        </div>

                        <div class="chart-container">
                            <div class="chart" id="chartdiv"></div>
                        </div>
                    </div>
                </div>
                <!-- /basic bar chart -->
            </div>
        </div>

        <!-- Resources -->
        <script src="https://www.amcharts.com/lib/4/core.js"></script>
        <script src="https://www.amcharts.com/lib/4/charts.js"></script>
        <script src="https://www.amcharts.com/lib/4/themes/animated.js"></script>


        <!-- Chart code -->
        <script>
            am4core.ready(function() {

                am4core.useTheme(am4themes_animated);

                var chart = am4core.create("chartdiv", am4charts.XYChart);
                chart.scrollbarX = new am4core.Scrollbar();

                chart.data = [
                        @foreach($ordersMyMonths as $pp)
                    {
                        "country": "<?= $pp->month?>",
                        "visits": <?= $pp->total ?>,
                    },
                    @endforeach
                ];

// Create axes
                var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
                categoryAxis.dataFields.category = "country";
                categoryAxis.renderer.grid.template.location = 0;
                categoryAxis.renderer.minGridDistance = 30;
                categoryAxis.renderer.labels.template.horizontalCenter = "right";
                categoryAxis.renderer.labels.template.verticalCenter = "middle";
                categoryAxis.renderer.labels.template.rotation = 270;
                categoryAxis.tooltip.disabled = true;
                categoryAxis.renderer.minHeight = 110;

                var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
                valueAxis.renderer.minWidth = 50;

// Create series
                var series = chart.series.push(new am4charts.ColumnSeries());
                series.sequencedInterpolation = true;
                series.dataFields.valueY = "visits";
                series.dataFields.categoryX = "country";
                series.tooltipText = "[{categoryX}: bold]{valueY}[/]";
                series.columns.template.strokeWidth = 0;

                series.tooltip.pointerOrientation = "vertical";

                series.columns.template.column.cornerRadiusTopLeft = 10;
                series.columns.template.column.cornerRadiusTopRight = 10;
                series.columns.template.column.fillOpacity = 0.8;

// on hover, make corner radiuses bigger
                var hoverState = series.columns.template.column.states.create("hover");
                hoverState.properties.cornerRadiusTopLeft = 0;
                hoverState.properties.cornerRadiusTopRight = 0;
                hoverState.properties.fillOpacity = 1;

                series.columns.template.adapter.add("fill", function(fill, target) {
                    return chart.colors.getIndex(target.dataItem.index);
                });

// Cursor
                chart.cursor = new am4charts.XYCursor();



                var chartLinks = am4core.create("chart_link", am4charts.PieChart);

                chartLinks.data = [
                        @foreach($links as $link)
                    {
                        "country": "<?= $link['domain'] ?>",
                        "litres": <?= $link['count'] ?>,
                    },
                    @endforeach
                ];

                var pieSeries = chartLinks.series.push(new am4charts.PieSeries());
                pieSeries.dataFields.value = "litres";
                pieSeries.dataFields.category = "country";
                pieSeries.innerRadius = am4core.percent(50);
                pieSeries.ticks.template.disabled = true;
                pieSeries.labels.template.disabled = true;

                var rgm = new am4core.RadialGradientModifier();
                rgm.brightnesses.push(-0.8, -0.8, -0.5, 0, - 0.5);
                pieSeries.slices.template.fillModifier = rgm;
                pieSeries.slices.template.strokeModifier = rgm;
                pieSeries.slices.template.strokeOpacity = 0.4;
                pieSeries.slices.template.strokeWidth = 0;

                chartLinks.legend = new am4charts.Legend();
                chartLinks.legend.position = "right";




                /*  Users  */


                /**
                 * Chart design taken from Samsung health app
                 */

                var chartUser = am4core.create("chart_user", am4charts.XYChart);
                chartUser.hiddenState.properties.opacity = 0; // this creates initial fade-in

                chartUser.paddingBottom = 30;

                chartUser.data = [
                        @foreach($groupedUsers as $user)
                    {
                        "name": "<?= $user['name'] ?>",
                        "steps": <?= $user['total'] ?>,
                        "href": "<?= asset(config('ase.default.avatar')) ?>"
                    },
                    @endforeach
                ];

                var categoryAxis = chartUser.xAxes.push(new am4charts.CategoryAxis());
                categoryAxis.dataFields.category = "name";
                categoryAxis.renderer.grid.template.strokeOpacity = 0;
                categoryAxis.renderer.minGridDistance = 10;
                categoryAxis.renderer.labels.template.dy = 35;
                categoryAxis.renderer.tooltip.dy = 35;

                var valueAxis = chartUser.yAxes.push(new am4charts.ValueAxis());
                valueAxis.renderer.inside = true;
                valueAxis.renderer.labels.template.fillOpacity = 0.3;
                valueAxis.renderer.grid.template.strokeOpacity = 0;
                valueAxis.min = 0;
                valueAxis.cursorTooltipEnabled = false;
                valueAxis.renderer.baseGrid.strokeOpacity = 0;

                var series = chartUser.series.push(new am4charts.ColumnSeries);
                series.dataFields.valueY = "steps";
                series.dataFields.categoryX = "name";
                series.tooltipText = "{valueY.value}";
                series.tooltip.pointerOrientation = "vertical";
                series.tooltip.dy = - 6;
                series.columnsContainer.zIndex = 100;

                var columnTemplate = series.columns.template;
                columnTemplate.width = am4core.percent(50);
                columnTemplate.maxWidth = 66;
                columnTemplate.column.cornerRadius(60, 60, 10, 10);
                columnTemplate.strokeOpacity = 0;

                series.heatRules.push({ target: columnTemplate, property: "fill", dataField: "valueY", min: am4core.color("#e5dc36"), max: am4core.color("#5faa46") });
                series.mainContainer.mask = undefined;

                var cursor = new am4charts.XYCursor();
                chart.cursor = cursor;
                cursor.lineX.disabled = true;
                cursor.lineY.disabled = true;
                cursor.behavior = "none";

                var bullet = columnTemplate.createChild(am4charts.CircleBullet);
                bullet.circle.radius = 30;
                bullet.valign = "bottom";
                bullet.align = "center";
                bullet.isMeasured = true;
                bullet.mouseEnabled = false;
                bullet.verticalCenter = "bottom";
                bullet.interactionsEnabled = false;

                var hoverState = bullet.states.create("hover");
                var outlineCircle = bullet.createChild(am4core.Circle);
                outlineCircle.adapter.add("radius", function (radius, target) {
                    var circleBullet = target.parent;
                    return circleBullet.circle.pixelRadius + 10;
                })

                var image = bullet.createChild(am4core.Image);
                image.width = 60;
                image.height = 60;
                image.horizontalCenter = "middle";
                image.verticalCenter = "middle";
                image.propertyFields.href = "href";

                image.adapter.add("mask", function (mask, target) {
                    var circleBullet = target.parent;
                    return circleBullet.circle;
                })

                var previousBullet;
                chartUser.cursor = new am4charts.XYCursor();
                chartUser.cursor.events.on("cursorpositionchanged", function (event) {
                    var dataItem = series.tooltipDataItem;

                    if (dataItem.column) {
                        var bullet = dataItem.column.children.getIndex(1);

                        if (previousBullet && previousBullet != bullet) {
                            previousBullet.isHover = false;
                        }

                        if (previousBullet != bullet) {

                            var hs = bullet.states.getKey("hover");
                            hs.properties.dy = -bullet.parent.pixelHeight + 30;
                            bullet.isHover = true;

                            previousBullet = bullet;
                        }
                    }
                })
            }); // end am4core.ready()
        </script>
    @endpermission

    <div class="row">
        <div class="col-xl-6 col-xl-offset-3">
            @include('admin.widgets.cells')
        </div>
    </div>
@endsection
