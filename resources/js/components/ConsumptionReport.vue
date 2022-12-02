<template>
    <div>
        <h1>{{ title }}</h1>
        <b-card>
        <b-form @submit="onSubmit" @reset="onReset">
            <b-form-row>
                <b-col>
                    <b-form-group label="Begin" label-cols="2">
                        <b-form-datepicker required id="begin-date" label-cols="3" label="Begin"
                                           v-model="beginDate" :state="validBeginDate"
                                           :date-format-options="{year: 'numeric', month: 'short', day: '2-digit'}"
                                           class="mb-2"/>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="End" label-cols="2">
                        <b-form-datepicker required id="end-date" label-cols="3" label="End"
                                           v-model="endDate" :state="validEndDate"
                                           :date-format-options="{year: 'numeric', month: 'short', day: '2-digit'}"
                                           class="mb-2" />
                    </b-form-group>
                </b-col>
                <b-col>

                </b-col>
            </b-form-row>

            <b-form-row>
                <b-col>
                    <b-form-group label="Buildings">
                        <v-select multiple
                                  v-model="selectedBuildings"
                                  :options="buildingOptions"
                                  placeholder="No building selected" />
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
                <b-button type="reset" variant="danger">Reset</b-button>
            </b-form-row>
        </b-form>
        </b-card>
    </div>
</template>

<script>

export default {
    name: "ConsumptionReport",
    props: ['title', 'meters', 'buildings'],
    data: function () {
        return {
            validated: false,
            beginDate: null,
            endDate: null,
            selectedBuildings: [],   // buildings selected by the user
            selectedMeters: [],      // meters selected by the user
        }
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
            return this.buildings.map(item => {
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
                begin: this.beginDate,
                end: this.endDate,
                meters: this.selectedMeterNames.join(',')
            }
        }
    },
    methods: {
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
                if (!currentNames.includes(item.name)) {
                    console.log('push ', item)
                    this.selectedMeters.push(item)
                }
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
                alert(searchParams.toString())
                window.location.search += searchParams;
            }

        },
        onReset(event) {
            event.preventDefault()
            // Reset our form values
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
