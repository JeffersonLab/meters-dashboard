<template>
    <div>
        <b-card no-body>
            <template #header>
                <div class="row">
                    <h4 class="mb-0 col-8" style="display: inline-block">
                        Consumption Charts
                    </h4>
                    <year-month-select class="mb-0 col-4" :month="month" :year="year" :min-year="minYear"
                                       @selectMonth="(v) => month=v"
                                       @selectYear="(v) => year=v">
                    </year-month-select>
                </div>
            </template>
            <b-tabs pills card vertical>
                <b-tab title="Power" v-if="hasPowerMeters">
                    <consumption-viewer
                        :model="building"
                        chart-type="dailykwh"
                        :start-date="startDate"
                        :end-date="endDate"
                    />
                </b-tab>
                <b-tab title="Water" v-if="hasWaterMeters">
                    <consumption-viewer
                        :model="building"
                        chart-type="dailygallons"
                        :start-date="startDate"
                        :end-date="endDate"
                    />
                </b-tab>
                <b-tab title="Gas" v-if="hasGasMeters">
                    <consumption-viewer
                        :model="building"
                        chart-type="dailyccf"
                        :start-date="startDate"
                        :end-date="endDate"
                    />
                </b-tab>
            </b-tabs>
        </b-card>
    </div>
</template>

<script>
import ConsumptionViewer from "./ConsumptionViewer";
import YearMonthSelect from "./YearMonthSelect.vue";
export default {
    name: "BuildingCharts",
    props: {
        meters: {type: Array},
        building: {type: Object, required: true}
    },
    components: {ConsumptionViewer,YearMonthSelect},
    data() {
        return {
            minYear: 2021,
            year: null,
            month: null,
        }
    },
    created() {
        this.year = new Date().getFullYear()
        this.month = new Date().getMonth()  // Remember js January is month 0 !!
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
        // The current month (standard 1-12)
        thisMonth(){
            return this.month +1
        },
        nextMonth(){
            return this.thisMonth < 12 ? this.thisMonth + 1 : 1
        },
        nextYear(){
          return this.year + 1
        },
        hasGasMeters() {
            return this.hasMeterType('gas')
        },
        hasPowerMeters() {
            return this.hasMeterType('power')
        },
        hasWaterMeters() {
            return this.hasMeterType('water')
        },
    },
    methods: {
        hasMeterType(type){
            return this.meters.find(item => item.type.toLowerCase() === type)
        }
    }
}
</script>

<style scoped>

</style>
