#!/usr/bin/env bash

/wait-for-it.sh db:5432 -- /usr/local/bin/php /create_data.php
supervisord -c /etc/supervisor/conf.d/worker.conf
