<template>
    <div>
        <b-card>
        <b-form @submit="onSubmit" @reset="onReset">
            <b-form-row>
                <b-col>
                    <b-form-group label="Begin" label-cols="2">
                        <b-form-datepicker required id="begin-date" label-cols="3" label="Begin"
                                           v-model="beginDate" :state="validBeginDate"
                                           :date-format-options="{year: 'numeric', month: 'short', day: '2-digit'}"
                                           class="mb-2"/>
                        <b-form-timepicker required id="begin-time" v-model="beginTime" />
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="End" label-cols="2">
                        <b-form-datepicker required id="end-date" label-cols="3" label="End"
                                           v-model="endDate" :state="validEndDate"
                                           :date-format-options="{year: 'numeric', month: 'short', day: '2-digit'}"
                                           class="mb-2" />
                        <b-form-timepicker required id="end-time" v-model="endTime" />
                    </b-form-group>
                </b-col>
            </b-form-row>

            <b-form-row>
                <b-col>
                    <b-form-group>
                        <template v-slot:label>
                            <label>Buildings</label>

                        </template>
                        <v-select multiple
                                  v-model="selectedBuildings"
                                  :options="buildingOptions"
                                  placeholder="No building selected" />
                        <template v-slot:description>
                            Note: options are limited to buildings with appropriate meter type.
                            Selecting or deselecting a building adds or removes all building meters from the
                            Meter control below.
                            <b-button @click="onAllBuildings" pill variant="info" size="sm">
                                Select All
                            </b-button>
                        </template>
                    </b-form-group>
                </b-col>
            </b-form-row>
            <b-form-row>
                <b-col>
                    <b-form-group class="meters-select" label="Meters" :state="validSelectedMeters" >
                        <v-select multiple
                                  :disabled="!hasSelectedBuildings"
                                  v-model="selectedMeters"
                                  :options="filteredMeterOptions"
                                  placeholder="No building selected">
                        </v-select>
                    </b-form-group>
                </b-col>
            </b-form-row>
            <b-form-row>
                <b-button type="submit" variant="primary">Submit</b-button>
                <b-button type="reset" variant="secondary">Reset</b-button>
                <b-button @click="onClear" type="button" variant="danger">Clear</b-button>
<!--                <b-button @click="onAllBuildings" type="button" variant="info">All Buildings</b-button>-->
            </b-form-row>
        </b-form>
        </b-card>
    </div>
</template>

<script>

export default {
    name: "ConsumptionReportFilters",
    props: ['request', 'title', 'meters', 'buildings'],
    data: function () {
        return {
            validated: false,
            beginDate: null,
            beginTime: null,
            endDate: null,
            endTime: null,
            selectedBuildings: [],   // buildings selected by the user
            selectedMeters: [],      // meters selected by the user
        }
    },
    // Upon creating we want to initialize the form fields with data from the request
    created(){
        this.initFromRequest()
    },
    watch: {
        // When a change happens to the selectedBuildings property, we will compare new and old values
        // to determine if the change was an addition or deletion and then update the selectedMeters property
        // appropriately to add or remove related items.
        selectedBuildingNames: function (newVal, oldVal) {
            if (newVal.length > oldVal.length) {    // item added.
                let differences = newVal.filter(x => !oldVal.includes(x))    // filter returns an array
                console.log('add ', differences[0])
                this.selectMetersOf(differences[0])                          // that should always be single element
            } else {                                 // item removed
                let differences = oldVal.filter(x => !newVal.includes(x))     // filter returns an array
                console.log('remove ', differences[0])
                this.removeMetersOf(differences[0])                           // that should always be single element
            }
        }
    },
    computed: {
        requestedMeters(){
          if (this.request.meters){
              // Use the meter names from the request to lookup
              // and return matching meter objects.
              let items = this.request.meters.split(',')
              return this.meterOptions.filter( meter => {
                  return items.includes(meter.epics_name)
              })
          }
          return []
        },
        validBeginDate(){
            if (this.validated){
                return this.beginDate != null
            }
            return null
        },
        validEndDate(){
            if (this.validated){
                return this.endDate != null
            }
          return null
        },
        validSelectedMeters(){
            if (this.validated){
                return this.hasSelectedMeters
            }
            return null
        },
        hasValidForm(){
          return this.validBeginDate && this.validEndDate && this.validSelectedMeters
        },
        // Available buildings to select
        buildingOptions() {
            return this.buildings
                .filter(item => {
                    // Only include buildings with meters available
                    return this.meterOptions.map( meter => meter.building).includes(item.name)
                })
                .map(item => {
                return {
                    label: item.building_num + ' ' + item.name,
                    id: item.id,
                    name: item.name
                }
            })
        },

        // Have any buildings been selected?
        hasSelectedBuildings() {
            return this.selectedBuildings.length > 0
        },
        // Return 1D array of just names of selected buildings
        selectedBuildingNames() {
            return this.selectedBuildings.map(item => item.name)
        },
        // Available meters to select
        meterOptions() {
            return this.meters.map(item => {
                return {
                    label: item.epics_name,
                    id: item.id,
                    epics_name: item.epics_name,
                    building: item.building
                }
            })
        },
        // Have any buildings been selected?
        hasSelectedMeters() {
            return this.selectedMeters.length > 0
        },
        // Return 1D array of just names of selected meters
        selectedMeterNames() {
            return this.selectedMeters.map(item => item.epics_name)
        },
        // meter options filtered by current building selections.
        filteredMeterOptions() {
            return this.meterOptions.filter(item => {
                return this.selectedBuildingNames.includes(item.building)
            })
        },
        //
        formData(){
            return {
                begin: this.beginDate + ' ' + this.beginTime,
                end: this.endDate + ' ' + this.endTime,
                meters: this.selectedMeterNames.join(',')
            }
        }
    },
    methods: {
        initFromRequest(){
            this.beginDate = this.request.begin ? this.dateOnly(this.request.begin) : null
            this.beginTime = this.request.begin ? this.timeOnly(this.request.begin) : null
            this.endDate = this.request.end ? this.dateOnly(this.request.end) : null
            this.endTime = this.request.end ? this.timeOnly(this.request.end) : null
            this.selectedMeters = this.requestedMeters
            this.initSelectedBuildings()
        },
        // Return the date portion of YYYY-MM-DD HH:MM string
        dateOnly(dateTimeStr){
            return dateTimeStr.split(' ')[0]
        },
        // Return the time portion of YYYY-MM-DD HH:MM string
        timeOnly(dateTimeStr){
            let time = dateTimeStr.split(' ')[1]
            return time ? time : '00:00'
        },
        initSelectedBuildings(){
            // Only meters are included in a request, so to initialize which buildings are selected
            // we must extract them from the attributes of the selected meters.
            this.selectedBuildings = this.buildingOptions.filter( building => {
                return this.selectedMeters.find( meter => {
                    return meter.building === building.name
                })
            })
        },
        // Returns the array of meters for buildingName
        metersOf(buildingName) {
            return this.meterOptions.filter(item => {
                return item.building === buildingName
            })
        },
        // Add meters of buildingName to the list of selected Meters
        selectMetersOf(buildingName) {
            let currentNames = this.selectedMeterNames.slice()       // make a copy before we start updating original
            this.metersOf(buildingName).forEach(item => {
                if (!currentNames.includes(item.epics_name)) {
                    console.log('push ', item)
                    this.selectedMeters.push(item)
                }
            })
        },
        selectAllBuildings(){
            this.selectedBuildings = []
            this.selectedMeters = []
            this.buildingOptions.forEach(item => {
                this.selectedBuildings.push(item)
                this.selectMetersOf(item.name)
            })
        },
        // Remove meters of buildingName to the list of selected Meters
        removeMetersOf(buildingName) {
            this.selectedMeters = this.selectedMeters.filter(item => item.building !== buildingName)
        },
        onSubmit(event) {
            event.preventDefault()
            this.validated = true
            if (this.hasValidForm){
                const searchParams = new URLSearchParams(this.formData);
                window.location.search = searchParams.toString();
            }
        },
        onReset(event) {
            event.preventDefault()
            // Reset our form values
            this.validated = false
            this.initFromRequest()
        },
        onAllBuildings(event){
            event.preventDefault()
            // clear our form values
            this.validated = false
            this.selectAllBuildings()
        },
        onClear(event){
            event.preventDefault()
            // clear our form values
            this.validated = false,
            this.beginDate = null,
            this.endDate = null,
            this.selectedMeters = [],
            this.selectedBuildings = []
        }
    }
}
</script>

<style>
.form-group.meters-select.is-invalid > div{
    border: 1px solid red;
}
.v-select {
    background: #ffffff;
}


</style>
