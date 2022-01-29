

@extends('layouts.default')

@section('title', 'Meters Home')

@push('js')

@endpush

@section('content')

    <div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Site Map</h3>
                <div class="box-tools pull-right">
                    <!-- Buttons, labels, and many other things can be placed here! -->
                    <!-- Here is a label for example -->
                    <span class="label label-primary">Label</span>
                </div>
                <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <img id="SiteMapImg" src="{{asset('img/site_map.png')}}" alt="Site Map" usemap="#SiteMap" />
                <map name="SiteMap" id="SiteMap">
                    <area alt="TED" title="TED" href="{!! route('buildings.show',['TechnologyEngineeringDevelopment']) !!}" shape="rect" coords="101,586,175,680" />
                    <area alt="ESR" title="ESR" href="{!! route('buildings.show',['EndStationRefrigeration']) !!}" shape="rect" coords="210,864,233,890" />
                    <area alt="TestLab" title="TestLab" href="{!! route('buildings.show',['TestLab']) !!}" shape="rect" coords="188,613,248,727" />
                    <area alt="NL" title="North Linac" href="{!! route('buildings.show',['NorthLinacService']) !!}" shape="rect" coords="352,762,591,777" />
                    <area alt="SA" title="South Access" href="{!! route('buildings.show',['SouthAccess']) !!}" shape="rect" coords="651,874,686,907" />
                    <area alt="CH" title="Counting House" href="{!! route('buildings.show',['CountingHouse']) !!}" shape="rect" coords="199,895,233,921" />
                    <area alt="A" title="Hall A" href="{!! route('buildings.show',['ExperimentalHallA']) !!}" shape="rect" coords="144,821,208,881" />
                    <area alt="B" title="Hall B" href="{!! route('buildings.show',['ExperimentalHallB']) !!}" shape="rect" coords="153,889,189,913" />
                    <area alt="C" title="Hall C" href="{!! route('buildings.show',['ExperimentalHallC']) !!}" shape="rect" coords="141,914,209,964" />
                    <area alt="D" title="Hall D" href="{!! route('buildings.show',['ExperimentalHallD']) !!}" shape="rect" coords="824,779,869,814" />
                    <area alt="CHD" title="HD Counting House" href="{!! route('buildings.show',['HallDCountingHouse']) !!}" shape="rect" coords="833,751,874,779" />
                    <area alt="LERF" title="LERF" href="{!! route('buildings.show',['LowEnergyRecirculatorFacility']) !!}" shape="rect" coords="586,822,668,854" />
                    <area alt="ExperimentalStaging" title="ExperimentalStaging" href="{!! route('buildings.show',['ExperimentalStaging']) !!}" shape="rect" coords="576,727,630,758" />
                    <area alt="GPB" title="GPB" href="{!! route('buildings.show',['GPB']) !!}" shape="rect" coords="645,916,685,937" />
                    <area alt="PhysicsStorage" title="PhysicsStorage" href="{!! route('buildings.show',['PhysicsStorage']) !!}" shape="rect" coords="354,929,393,956" />
                    <area alt="CUP" title="Central Utility Plant" href="{!! route('buildings.show',['CentralUtilityPlant']) !!}" shape="rect" coords="150,741,194,772" />
                    <area alt="ESHQ" title="ESHQ" href="{!! route('buildings.show',['ESHQ']) !!}" shape="rect" coords="286,689,313,734" />
                    <area alt="CEBAFCenter" title="CEBAFCenter" href="{!! route('buildings.show',['CEBAFCenter']) !!}" shape="rect" coords="83,378,190,480" />
                    <area alt="SSC" title="SSC" href="{!! route('buildings.show',['SupportServiceCenter']) !!}" shape="rect" coords="132,178,225,241" />
                    <area alt="PhysicsFabrication" title="PhysicsFabrication" href="{!! route('buildings.show',['PhysicsFabrication']) !!}" shape="rect" coords="246,892,279,920" />
                    <area alt="CHL" title="CHL" href="{!! route('buildings.show',['EndStationService']) !!}" shape="rect" coords="117,847,140,880" />

                    <area alt="EndStationService" title="CentralHeliumLiquifier" href="{!! route('buildings.show',['CentralHeliumLiquifier']) !!}" shape="rect" coords="460,824,524,858" />
                    <area alt="SL" title="South Linac" href="{!! route('buildings.show',['SouthLinacService']) !!}" shape="rect" coords="407,900,637,917" />
                    <area alt="NA" title="North Access" href="{!! route('buildings.show',['NorthAccess']) !!}" shape="rect" coords="312,776,344,810" />
                    <area alt="AMSB" title="AMSB" href="{!! route('buildings.show',['AcceleratorMaintenanceSupport']) !!}"  coords="414,783,452,807" shape="rect">

                </map>
            </div>
            <!-- /.box-body -->
            <div class="box-footer">

            </div>
            <!-- box-footer -->
        </div>
        <!-- /.box -->
    </div>
    </div>


        @stop

@section('css')

@stop

@section('js')
    <script src="{{ asset('js/jquery.maphilight.min.js') }}"></script>
<script>
       $("#SiteMapImg").maphilight();

</script>
@stop
