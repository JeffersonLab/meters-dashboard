<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 1/30/18
 * Time: 11:16 AM
 */

namespace App\Alerts;

interface AlertInterface
{
    /**
     * Identifies the type of alert
     *
     * @return string
     */
    public function type();

    /**
     * The nature of the alert/check performed
     *
     * @return string
     */
    public function description();

    /**
     * Message or text output from the check that can be displayed to a user
     *
     * @return string
     */
    public function message();

    /**
     * The alert status
     *
     * @return string  critical, warning, etc.
     */
    public function status();

    /**
     * Whether the alert is acknowledged or not
     *
     * @return string
     */
    public function isAcknowledged();

    /**
     * The unix timestamp of when the alert status was last checked
     *
     * @return int
     */
    public function lastCheck();

    /**
     * The meter to which the alert applies
     *
     * @return string
     */
    public function meter();
}
