<template>
<div style="width:100%">
    <div style="display: flex; justify-content: center; align-items: center; height: 360px; width: 100%;" v-if="! hasData">
        <b-spinner label="Loading..."></b-spinner>
    </div>
    <div style="display: flex; justify-content: center; align-items: center; height: 360px; width: 100%;" v-if="hasEmptyData">
        <p>Empty Data Set</p>
    </div>
    <consumption-chart v-if="hasData" :title="title" :data="data" :id="chartId"/>
</div>

</template>

<script>
import ConsumptionChart from "./chart/ConsumptionChart";
export default {
    name: "ConsumptionViewer",
    props: {
      model: {required: true, type: Object},   // Of type building or meter
      chartType: {required: true},             // see ChartFactory for options
      startDate: {required: true},             // YYYY-MM-DD
      endDate: {required: true}                 // YYYY-MM-DD
    },
    components: { ConsumptionChart },
    mounted() {
        this.getData()
    },
    watch: {
        // When the selected date range changes, we need to fetch fresh data
        startDate(newState, oldState){
            this.data = []
            this.getData()
        },
    },
    computed: {
        chartId(){
          return this.chartType + '_' + this.model.id
        },
        hasData(){
            return this.data.length > 0
        },
        hasEmptyData(){
            return this.data.length === 0 && ! this.isLoading
        },
        queryUrl(){
          // The route() helper used below was imported as a global helper
          // via the @ziggy blade directive in layouts.default template
          if (this.model.type.toLowerCase() === 'building'){
              return route('buildings.chart_data')
          }else{
              return route('meters.chart_data')
          }
        },
        queryParams(){
            return {
                model_id: this.model.id,
                chart: this.chartType,
                start: this.startDate,
                end: this.endDate
            }
        }
    },
    data() {
        return {
            isLoading: true,
            title: {
                text: "Consumption Chart"
            },
            data: []
        }
    },
    methods: {
        getData(){
            this.isLoading = true
            this.$http.get(this.queryUrl,{params:this.queryParams}).then((res) => {
                this.data = res.data.data.data  //fist data is axios, 2nd is API, 3rd is chartjs
                this.title = res.data.data.title  //fist data is axios, 2nd is API
                this.isLoading = false
            })
        }
    }
}
</script>

<style scoped>

</style>
