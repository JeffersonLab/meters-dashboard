<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 10/24/17
 * Time: 2:46 PM
 */    // goal:
//   Goal Buildings are those that require special DOE reporting. The
//   necessary PVs to query are identified here.  We can't simply list the
//   buildings, because some reporting is per-wing, for example CEBAF Center
//   reporting must separate A/B/C vs. F wing.
//

return
    [
        'goal_buildings' => [
            'VARC' => [
                'description' => 'VARC/SSC (28)',
                'gasBTU' => '',
                'powerBTU' => '',
                'area' => '',
            ],
            'CEBAFCenterABC' => [
                'description' => 'CEBAF Center A/B/C wings',
                'gasBTU' => '',
                'powerBTU' => '',
                'area' => '',
            ],
            'CEBAFCenterF' => [
                'description' => 'CEBAF Center F wing',
                'gasBTU' => '',
                'powerBTU' => '',
                'area' => '',
            ],
            'TechnologyEngineeringDevelopment' => [
                'description' => 'Technology Engineering Development (TED) (55)',
                'gasBTU' => '',
                'powerBTU' => '',
                'area' => '',
            ],
            'ExperimentalStaging' => [
                'description' => 'Experimental Staging (23)',
                'gasBTU' => '',
                'powerBTU' => 'ExperimentalStaging:totBTU',
                'area' => '',
            ],
            'PhysicsStorage' => [
                'description' => 'Physics Storage (72)',
                'gasBTU' => '',
                'powerBTU' => 'PhysicsStorage:totBTU',
                'area' => '',
            ],
            'GeneralPurposeBuilding' => [
                'description' => 'GeneralPurposeBuilding (36)',
                'gasBTU' => '',
                'powerBTU' => 'GeneralPurposeBuilding:totBTU',
                'area' => '',
            ],
            'AcceleratorMaintenanceSupport'=> [
                'description' => 'Accelerator Maintenance Support (87)',
                'gasBTU' => '',
                'powerBTU' => 'AcceleratorMaintenanceSupport:totBTU',
                'area' => '',
            ],
            'AcceleratorTechnicalSupport'=> [
                'description' => 'Accelerator Technical Support (89)',
                'gasBTU' => '',
                'powerBTU' => 'AcceleratorTechnicalSupport:totBTU',
                'area' => '',
            ],
            'ESHQ'=> [
                'description' => 'ESH&Q Building (52)',
                'gasBTU' => '',
                'powerBTU' => '',
                'area' => '',
            ],
        ]
    ];
