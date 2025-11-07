@extends('layouts.app')

@section('header_css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@4.4.0/dist/apexcharts.css">
    <style>
        .stat-card { min-height: 120px; }
        .chart-card { min-height: 360px; }
    </style>
@endsection

@section('main_content')


                
                        @include('components.card')

                    
                        <!-- start 2nd cards-->
                        <div class="row g-3">
                            <div class="col-lg-7">
                                <div class="card chart-card">
                                    <div class="card-header">
                                        <h5 class="card-title">Monthly Supply Cost (Last 12 months)</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="chartSupplyCost"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="card chart-card">
                                    <div class="card-header">
                                        <h5 class="card-title">Top 5 Companies by Spend (This Year)</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="chartTopCompanies"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- End second card-->



                        
            <!-- Row 3: Top Products Insights -->
            <div class="row">
                <!-- Top 10 Products Table -->
                <div class="col-lg-7 col-md-12">
                    <div class="card" style="min-height: 485px">
                        <div class="card-header card-header-text">
                            <h4 class="card-title">Top 10 Products (By Supply Cost)</h4>
                            <p class="category">Based on all time supply history</p>
                        </div>

                        <div class="card-content table-responsive">
                            <table class="table table-hover">
                                <thead class="text-primary">
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>Latest Price (Rs)</th>
                                        <th>Total Qty</th>
                                        <th>Total Cost (Rs)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($top10Products as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->product->name ?? 'N/A' }}</td>
                                            <td>
                                                @if($item->product->activePrice)
                                                    {{ number_format($item->product->activePrice->price, 2) }}
                                                @else
                                                    <span class="text-muted">No price</span>
                                                @endif
                                            </td>
                                            <td>@php
                                                
                                                $q = $item->total_qty;
                                                echo fmod($q, 1) == 0
                                                    ? number_format($q, 0)
                                                    : rtrim(rtrim(number_format($q, 3, '.', ''), '0'), '.');
                                                @endphp </td>
                                            <td>{{ number_format($item->total_cost, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No records found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Top 5 Products Pie Chart -->
                <div class="col-md-5">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <strong>Top 5 Products Breakdown</strong>
                        </div>
                        <div class="card-body">
                            <div id="chartProductsSupplied" style="min-height:350px;"></div>
                        </div>
                    </div>
                </div>
            </div>
            
                        
                        

     @endsection             
     
     @section('footer_js_links')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@4.4.0"></script>
    @endsection

    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Data from controller
    var months = {!! json_encode($months) !!};
    var supplyCostSeries = {!! json_encode($supplyCostSeries) !!};
    var productsSuppliedSeries = {!! json_encode($productsSuppliedSeries) !!};
    var topCompaniesLabels = {!! json_encode($topCompaniesLabels) !!};
    var topCompaniesSeries = {!! json_encode($topCompaniesSeries) !!};

    // 1) Monthly Supply Cost - area/line
    var optionsCost = {
        chart: { type: 'area', height: 350, toolbar: { show: true } },
        series: [{ name: 'Supply Cost', data: supplyCostSeries }],
        xaxis: { categories: months },
        yaxis: { labels: { formatter: function (val) { return val ? `Rs ${val.toFixed(0)}` : 'Rs 0';  } } },
        tooltip: { y: { formatter: (val) => `Rs ${parseFloat(val).toFixed(2)}` } },
        stroke: { curve: 'smooth' },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.6, opacityTo: 0.1 } },
        theme: { mode: 'light' },
    };
    var chartCost = new ApexCharts(document.querySelector("#chartSupplyCost"), optionsCost);
    chartCost.render();

    // // 2) Products Supplied - column
    // var optionsQty = {
    //     chart: { type: 'bar', height: 320 },
    //     series: [{ name: 'Qty Supplied', data: productsSuppliedSeries }],
    //     xaxis: { categories: months },
    //     yaxis: { labels: { formatter: function(val){ return val ? val.toFixed(0) : '0'; } } },
    //     plotOptions: { bar: { columnWidth: '60%' } },
    //     theme: { mode: 'light' },
    // };
    // var chartQty = new ApexCharts(document.querySelector("#chartProductsSupplied"), optionsQty);
    // chartQty.render();

    // 3) Top Companies - horizontal bar
    var optionsCompanies = {
        chart: { type: 'bar', height: 320 },
        series: [{ name: 'Spend', data: topCompaniesSeries }],
        xaxis: { labels: { formatter: function (val) { return val ? `$${parseFloat(val).toFixed(2)}` : '$0.00' } } },
        plotOptions: { bar: { horizontal: false } },
        dataLabels: { enabled: false },
        xaxis: { categories: topCompaniesLabels },
        theme: { mode: 'light' },
    };
    var chartCompanies = new ApexCharts(document.querySelector("#chartTopCompanies"), optionsCompanies);
    chartCompanies.render();


    
            const pieSeries = @json($pieSeries);
            const pieLabels = @json($pieLabels);

            // ✅ Top 5 Products Pie Chart
            new ApexCharts(document.querySelector("#chartProductsSupplied"), {
                chart: { type: 'pie', height: 350 },
                series: pieSeries,
                labels: pieLabels,
                legend: { position: 'bottom' },
                theme: { mode: 'light' }
            }).render();


            

});

</script>