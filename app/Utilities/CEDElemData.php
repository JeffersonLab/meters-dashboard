<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 7/24/17
 * Time: 2:21 PM
 */

namespace App\Utilities;

use Illuminate\Support\Collection;

class CEDElemData extends CEDData
{
    public $elem;

    public function __construct($elem = null)
    {
        parent::__construct();
        $this->elem = $elem;
    }

    /**
     * Returns a collection of CED element objects
     *
     * @return mixed
     *
     * @internal param $name
     */
    public function getData()
    {
        $data = $this->httpGet();

        return $data->Inventory->elements[0];
    }

    /**
     * Returns the query parameters expected by mySampler
     * as an array.
     */
    public function query(): array
    {
        return [
            'wrkspc' => $this->workspace,
            'e' => $this->elem,
            'out' => 'json',
        ];
    }
}
