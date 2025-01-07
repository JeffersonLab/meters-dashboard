<?php

/**
 * Register macro and content extensions of the laravelcollective HtmlBuilder.
 */

/**
 * Returns the specified string wrapped in the syntax of an EPICS Macro variable
 *   $str = 'foo' returns $(foo)
 *
 * @param  $str  string to be wrapped
 * @return string
 */
function epics_macro_variable($str)
{
    return '$('.$str.')';
}

// Approximation of the link_to_route helper that Laravel used to include
function link_to_route(string $name, ?string $text = null, array $parameters = []): string {
    $routeStr = route($name,$parameters);
    $linkText = $text ?? $routeStr;
    return "<a href='{$routeStr}'>{$linkText}</a>";
}
