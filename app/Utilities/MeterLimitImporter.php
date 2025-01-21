<?php

namespace App\Utilities;


use Illuminate\Support\Facades\Storage;

/**
 * Import a CSV file into meter_limits table
 */
class MeterLimitImporter {

    public $file;

    // The headers expected in first row of csv file
    public $header = [
        "meter",
        "field",
        "interval",
        "low",
        "lolo",
        "high",
        "hihi",
        "source",
        "comments",
    ];

    public $data = [];

    public function import($file) {
        $this->data = [];
        $this->file = Storage::disk('local')->get("imports/$file");
        $this->extractData();
        return $this->data;
    }

    public function hasHeader() {
        return !empty($this->header);
    }

    public function extractData($populateHeader=true) {
        $count = 0;
        foreach (explode("\n",$this->file) as $line) {
            $count++;
            if ($count === 1 && $populateHeader) {
                $this->header = str_getcsv($line, ",");
            }else{
                if (! empty($line)){
                    $data = str_getcsv($line, ",");
                    if ($this->hasHeader()){
                        $this->data[] = array_combine($this->header,$data);
                    }else{
                        $this->data[] = $data;
                    }
                }
            }
        }
    }

}
