<?php

namespace App\Providers;

use App\Alerts\MeterAlertRepository;
use App\Alerts\ServiceAlertRepository;
use App\Models\Buildings\Building;
use App\Models\Meters\Meter;
use App\Utilities\NagiosServicelist;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * How long to cache menu items (seconds).
     *
     * @var int
     */
    public $ttl = 30;

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        // Listen for menu being built event so that we can inject dynamic items into it.
        Event::listen(BuildingMenu::class, function (BuildingMenu $event) {

            $event->menu->add(
                [   'text' => 'Alerts',
                    'url' => '/alerts',
                    'icon' => 'warning',
                    'icon_color' => 'orange',
                    'label' => $this->getAlertLabel(),
                ]);


            $event->menu->add(
                [
                    'text' => 'Site Map',
                    'icon' => 'fas fa-fw fa-map',
                    'url' => route('buildings.index'),
                ]);


            $event->menu->add(['header' => 'READOUTS']);

            $event->menu->add(
                [
                    'text' => 'Power',
                    'icon' => config('meters.icons.power.symbol'),
                    'icon_color' => config('meters.icons.power.color'),
                    'submenu' => [
                        [   'text' => 'kWh',
                            'url' => route('monitor', ['power-kwh']),
                        ],
                        [   'text' => 'kW',
                            'url' => route('monitor', ['power-kw']),
                        ],
                        [   'text' => 'Voltage',
                            'url' => route('monitor', ['power-volt-avg']),
                        ],
                    ]
                ]
            );

            $event->menu->add(
                [
                    'text' => 'Water',
                    'icon' => config('meters.icons.water.symbol'),
                    'icon_color' => config('meters.icons.water.color'),
                    'submenu' => [
                        [   'text' => 'Cumulative (gal)',
                            'url' => route('monitor', ['water-gal']),
                        ],
                        [   'text' => 'Current (gpm)',
                            'url' => route('monitor', ['water-gpm']),
                        ],
                    ]
                ]
            );

            $event->menu->add(
                [
                    'text' => 'Gas',
                    'icon' => config('meters.icons.gas.symbol'),
                    'icon_color' => config('meters.icons.gas.color'),
                    'submenu' => [
                        [   'text' => 'Cumulative (ccf)',
                            'url' => route('monitor', ['gas-ccf']),
                        ],
                        [   'text' => 'Current (ccfpm)',
                            'url' => route('monitor', ['gas-ccfpm']),
                        ],
                    ]
                ]
            );

            $event->menu->add(['header' => 'BUILDINGS']);

            $items = $this->buildingMenuItems();
            $event->menu->add(
                [
                    'text' => 'Building List',
                    'icon' => 'fas fa-fw fa-building',
                    'label' => $items->count(),
                    'submenu' => $items->toArray()
                ]
            );
        });
    }

    /**
     * Return the collection of items to build the menu containing
     * buildings with their meters nested below.
     *
     * @return \Illuminate\Support\Collection|mixed|static
     */
    protected function buildingMenuItems(){
        if (Cache::has('menu-building-items')) {
            return Cache::get('menu-building-items');
        }

        $buildings = Building::with('meters')->get();
        $items = $buildings->sortBy('building_num')->map(function (Building $building) {
            return $this->buildingMenuItem($building);
        });
        Cache::put('menu-building-items', $items, $this->ttl);
        return $items;

    }

    public function buildingMenuItem(Building $building)
    {
        $item = [
            'text' => $building->getPresenter()->menuLabel(),
            'submenu' => [
                [   'text' => $building->getPresenter()->reportLabel(),
                    'icon' => 'fas fa-fw fa-building',
                    'url' => route('buildings.show', $building->name)]
            ]
        ];

        foreach ($building->powerMeters()->get()->all() as $meter){
            $item['submenu'][] = [
                'icon' => config('meters.icons.power.symbol'),
                'icon_color' => config('meters.icons.power.color'),
                'text' => $meter->epics_name,
                'url' => route('meters.show', $meter->id),
            ];
        }

        foreach ($building->waterMeters()->get()->all() as $meter){
            $item['submenu'][] = [
                'icon' => config('meters.icons.water.symbol'),
                'icon_color' => config('meters.icons.water.color'),
                'text' => $meter->epics_name,
                'url' => route('meters.show', $meter->id),
            ];
        }

        foreach ($building->gasMeters()->get()->all() as $meter){
            $item['submenu'][] = [
                'icon' => config('meters.icons.gas.symbol'),
                'icon_color' => config('meters.icons.gas.color'),
                'text' => $meter->epics_name,
                'url' => route('meters.show', $meter->id),
            ];
        }
        return $item;
    }

    public function meterMenuItem(Meter $meter)
    {
        return [
            'text' => $meter->epics_name,
            'url' => route('meters.show', $meter->id)
        ];
    }


    public function getAlertLabel(){
        if (env('APP_ENV') == 'testing'){
            return 'X';
        }
        if (Cache::has('menu-alert-label')) {
            return Cache::get('menu-alert-label');
        }
        try{
            $count = 0;
            $serviceAlertRepo = new ServiceAlertRepository(new NagiosServicelist());
            $count += $serviceAlertRepo->alerts()->count();
            $meterAlertRepo = new MeterAlertRepository();
            $count += $meterAlertRepo->alerts()->count();
            $label = ($count ? $count : '');
            Cache::put('menu-alert-label', $label, $this->ttl);
            return $label;
        }catch (\Exception $e){
            Log::error($e);
        }
        return '!';
    }
}
