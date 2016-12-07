# Vnstat Frontend
A simple HTML view of the data collected by the [vnstat traffic monitoring tool](http://humdi.net/vnstat/).

[![Build Status](https://ci.jacekk.co.uk/buildStatus/icon?job=Vnstat Frontend)](https://ci.jacekk.co.uk/job/Vnstat Frontend)

## Installation
All config is taken directly from vnstat so there is nothing to configure. Clone or download the repo and extract it somewhere that a browser can get to

## Testing
We use phpcs and phpunit for testing, run both before commiting anything
~~~
./core/vendor/bin/phpcs -p --standard=./ruleset.xml .
~~~
~~~
./core/vendor/bin/phpunit -c ./phpunit.xml
~~~

phpunit will do code coverage checking which requires xdebug, if it's not installed this will fail gracefully - not to worry.

A report of the test coverage is published [here by Jenkins](https://ci.jacekk.co.uk/view/Websites/job/Vnstat%20Frontend/HTML_Code_Coverage/index.html)
