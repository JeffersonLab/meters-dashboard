<?php

/**
 * Register macro and content extensions of the laravelcollective HtmlBuilder.
 */

/**
 * Returns the specified string wrapped in the syntax of an EPICS Macro variable
 *   $str = 'foo' returns $(foo)
 *
 * @param $str string to be wrapped
 * @return string
 */
function epics_macro_variable($str)
{
    return '$('.$str.')';
}
