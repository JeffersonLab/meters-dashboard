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
                    'url' => route('buildings.map'),
                ]);

            $event->menu->add(
                [
                    'text' => 'Building Status',
                    'icon' => 'fas fa-fw fa-building',
                    'url' => route('buildings.index'),
                ]);

            $event->menu->add(['header' => 'READOUTS']);

            $event->menu->add(
                [
                    'text' => 'Power',
                    'icon' => config('meters.icons.power.symbol'),
                    'icon_color' => config('meters.icons.power.color'),
                    'url' => route('monitor', ['power']),
                ]
            );

            $event->menu->add(
                [
                    'text' => 'Water',
                    'icon' => config('meters.icons.water.symbol'),
                    'icon_color' => config('meters.icons.water.color'),
                    'url' => route('monitor', ['water']),
                ]
            );

            $event->menu->add(
                [
                    'text' => 'Gas',
                    'icon' => config('meters.icons.gas.symbol'),
                    'icon_color' => config('meters.icons.gas.color'),
                    'url' => route('monitor', ['gas']),
                ]
            );
            $event->menu->add(['header' => 'SUBSTATIONS']);

            $event->menu->add(
                [
                    'text' => 'Substation Summary',
                    'icon' => config('meters.icons.power.symbol'),
                    'icon_color' => config('meters.icons.power.color'),
                    'url' => route('buildings.substation_summary'),
                ]);

            foreach ($this->substationMenuItems()->all() as $substation){
                $event->menu->add($substation);
            }


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

            $event->menu->add(['header' => 'COOLING TOWERS']);
            foreach ($this->coolingTowerMenuItems()->all() as $tower){
                $event->menu->add($tower);
            }

        });
    }

    /**
     * Return the collection of items for the Buildings menu.
     *
     * @return \Illuminate\Support\Collection|mixed|static
     */
    protected function buildingMenuItems(){
        return $this->buildingsOfType('Building')->sortBy('building_num',SORT_NATURAL)
            ->map(function (Building $building) {
                return $this->buildingMenuItem($building);
            });
    }

    /**
     * Return the collection of items for the Substations menu.
     *
     * @return \Illuminate\Support\Collection|mixed|static
     */
    protected function substationMenuItems(){
        return $this->buildingsOfType('Substation')->sortBy('building_num')
            ->map(function (Building $building) {
                return $this->substationMenuItem($building);
            });
    }

    /**
     * Return the collection of items for the Cooling Towers menu.
     *
     * @return \Illuminate\Support\Collection|mixed|static
     */
    protected function coolingTowerMenuItems(){
        return $this->buildingsOfType('CoolingTower')->sortBy('name',SORT_NATURAL)
            ->map(function (Building $building) {
                return $this->coolingTowerMenuItem($building);
        });
    }

    /**
     * Return a collection of buildings of specified type .
     *
     * @return \Illuminate\Support\Collection|mixed|static
     */
    protected function buildingsOfType($type){
        return Building::where('type',$type)->get();
    }

    /**
     * Return array representation of a substation menu item.
     *
     * @param Building $substation  - substations are a type of building
     * @return array
     */
    public function substationMenuItem(Building $substation)
    {
        return [
            'text' => $substation->name,
            'icon' => 'fas fa-fw fa-gopuram',
            'url' => route('buildings.show', $substation->name)
        ];
    }

    /**
     * Return array representation of a building menu item.
     *
     * @param Building $building
     * @return array
     */
    public function buildingMenuItem(Building $building)
    {
        return [
            'text' => $building->getPresenter()->menuLabel(),
            'icon' => 'fas fa-fw fa-building',
            'url' => route('buildings.show', $building->name)
        ];
    }


    /**
     * Return array representation of a substation menu item.
     *
     * @param Building $tower  - substations are a type of building
     * @return array
     */
    public function coolingTowerMenuItem(Building $tower)
    {
        return [
            'text' => $tower->name,
            'icon' => 'fas fa-fw fa-gopuram',
            'url' => route('cooling_towers.show', $tower->name)
        ];
    }

    public function getAlertLabel(){
        if (config('app.env') == 'testing' || config('app.env') == 'local'){
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
