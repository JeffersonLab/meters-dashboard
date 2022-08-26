<template>
    <div>
        <b-card no-body>
            <b-tabs pills card vertical>
                <b-tab title="Power" v-if="hasPowerMeters">
                    <consumption-viewer
                        :model="building"
                        chart-type="dailykwh"
                        start-date="2022-07-01"
                        end-date="2022-08-01"
                    />
                </b-tab>
                <b-tab title="Water" v-if="hasWaterMeters">
                    <consumption-viewer
                        :model="building"
                        chart-type="dailygallons"
                        start-date="2022-07-01"
                        end-date="2022-08-01"
                    />
                </b-tab>
                <b-tab title="Gas" v-if="hasGasMeters">
                    <consumption-viewer
                        :model="building"
                        chart-type="dailyccf"
                        start-date="2022-07-01"
                        end-date="2022-08-01"
                    />
                </b-tab>
            </b-tabs>
        </b-card>
    </div>
</template>

<script>
import ConsumptionViewer from "./ConsumptionViewer";
export default {
    name: "BuildingCharts",
    props: {
        meters: {type: Array},
        building: {type: Object, required: true}
    },
    components: {ConsumptionViewer},
    computed: {
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
