<template>
    <b-card>
        <template #header>
            <h4 class="mb-0">
                <i class="fas fa-fw fa-building text-black"></i>
                Site Building Status
                <epics-web-status-icon style="float:right" :epics-web-status="epicsWebStatus"></epics-web-status-icon>
            </h4>
        </template>
        <b-table class="meter-data" small
                 :items="buildingItems"
                 :fields="fields"
                 :sort-by.sync="sortBy"
                 :sort-desc.sync="sortDesc">

            <!-- A custom formatted meter name column -->
            <template #cell(number)="data">
                <b-link target="_blank" :href="buildingLink(data.item.id)">{{data.value}}</b-link>
            </template>

            <template #cell(name)="data">
                <b-link target="_blank" :href="buildingLink(data.item.id)">{{data.value}}</b-link>
            </template>

            <!-- A custom formatted column -->
            <template #cell(status)="data">
                <alarm-light :status="data.item.status" />
            </template>


        </b-table>

    </b-card>
</template>

<script>
import meterStatusTableMixin from "../mixin/meter-status-table-mixin";
import AlarmLight from "./AlarmLight";
export default {
    name: "BuildingAlertTable",
    components: {AlarmLight},
    mixins: [meterStatusTableMixin],
    data() {
        return {
            sortBy: 'status',
            sortDesc: true,
            fields: [
                {
                    key: 'number',
                    sortable: true,
                },
                {
                    key: 'name',
                    sortable: true,
                },
                {
                    key: 'status',
                    sortable: true,
                    formatter: (value, key, item) => {
                        if (value.value === null || value.value === undefined || value.value === ''){
                            return -1
                        }
                        return Math.floor(value.value)  // 0, 1, 2 for no, minor, major alarm of :alrmSum
                    },
                    sortByFormatted: true,
                },
            ]
        }
    },
    computed: {
        buildingItems() {
            return this.meters.map(item => {return {
                id: item.id,
                number: item.buildingNumber,
                name: item.epics_name,
                status: this.status(item.epics_name)
            }})
        }
    },
    methods: {
        status(buildingName){
            return this.pvState(buildingName+'_alrmSum')
        }
    }
}
</script>

<style scoped>

</style>
