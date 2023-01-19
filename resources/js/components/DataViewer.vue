<template>
<div>
    <b-card>
        <template #header>
            <div class="row">
                <h4 class="mb-0 col-7" style="display: inline-block">
                    Data Table
                </h4>
                <b-form-select class="mb-0 col-2" :options="monthOptions" v-model="month"></b-form-select>
                <b-form-select class="mb-0 col-2" :options="yearOptions" v-model="year"></b-form-select>
                <b-form-select class="mb-0 col-1" :options="granularityOptions" v-model="granularity"></b-form-select>
            </div>
        </template>

    <div style="display: flex; justify-content: center; align-items: center; height: 360px; width: 100%;" v-if="! hasData">
        <b-spinner label="Loading..."></b-spinner>
    </div>
    <div>
        <b-pagination
            v-if="hasPages"
            v-model="currentPage"
            :total-rows="rows"
            :per-page="perPage"
            aria-controls="my-table"
        ></b-pagination>
        <b-table v-if="hasData" :title="title" :items="data" :fields="fields" :per-page="perPage" :current-page="currentPage" small/>
    </div>
    </b-card>
</div>

</template>

<script>
import DataTable from "./DataTable.vue";
export default {
    name: "DataViewer",
    props: {
      model: {required: true, type: Object},   // Of type building or meter
    },
    components: { DataTable },
    created() {
        this.year = new Date().getFullYear()
        this.month = new Date().getMonth()  // Remember js January is month 0 !!
        // Populate the monthOptions
        this.monthOptions = Array.from({length: 12}, (e, i) => {
            let date = new Date(null, i + 1, null).toLocaleDateString("en", {month: "short"});
            return {text: date, value: i}
        })
        // Populate the yearOptions
        let maxYear = this.thisMonth === 12 ? this.year + 1 : this.year  // to support end date of nextYear-01-01
        for (let i=this.minYear; i <= maxYear; i++){
            this.yearOptions.push(i)
        }
    },
    mounted() {
        this.getData()
    },
    watch: {
        // When the selected date range changes, we need to fetch fresh data
        startDate(newState, oldState){
            this.data = []
            this.currentPage = 1
            this.getData()
        },
        granularity(newState, oldState){
            this.data = []
            this.currentPage = 1
            this.getData()
        },
    },
    computed: {
        startDate() {
            return `${this.year}-${this.thisMonth}-01`
        },
        endDate() {
            if (this.nextMonth === 1){
                return `${this.nextYear}-${this.nextMonth}-01`
            }
            return `${this.year}-${this.nextMonth}-01`
        },
        perPage(){
          switch (this.granularity){
              case 'all': return 48
              case 'hourly': return 24
              case 'daily': return 32
          }
          return 50
        },
        // The current month (standard 1-12)
        thisMonth(){
            return this.month +1
        },
        nextMonth(){
            return this.thisMonth < 12 ? this.thisMonth + 1 : 1
        },
        tableId(){
          return 'data_table_' + this.model.id
        },
        hasData(){
            return this.data.length > 0
        },
        hasPages(){
          return this.rows > this.perPage
        },
        rows(){
            return this.data.length
        },
        fields(){
          let fields = [{
              key: 'date',
              sortable: true,
              formatter: value => {
                  // see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/DateTimeFormat/DateTimeFormat#options
                  return (new Date(value)).toLocaleString('sv-SE',{dateStyle: "short", timeStyle: "short"})
              }
          },
          ]
          this.columns.forEach(item => {
              if (item !== 'id' && item !== 'date'){
                  fields.push({key: item})
              }
          })
          return fields
        },
        queryUrl(){
          // The route() helper used below was imported as a global helper
          // via the @ziggy blade directive in layouts.default template
          if (this.model.type.toLowerCase() === 'building'){
              //return route('buildings.chart_data')
          }else{
              return route('meters.table_data')
          }
        },
        queryParams(){
            return {
                model_id: this.model.id,
                start: this.startDate,
                end: this.endDate,
                granularity: this.granularity
            }
        }
    },
    data() {
        return {
            minYear: 2021,
            year: null,
            month: null,
            granularity: 'daily',
            yearOptions: [],
            monthOptions: [],
            granularityOptions:['daily','hourly','all'],
             title: {
                text: "Data Table"
            },
            columns: [],
            data: [],
            currentPage: 1,
        }
    },
    methods: {
        getData(){
            this.$http.get(this.queryUrl,{params:this.queryParams}).then((res) => {
                this.data = res.data.data.data  //fist data is axios, 2nd is API, 3rd is payload
                this.columns = res.data.data.columns
            })

        }
    }
}
</script>

<style scoped>

</style>
