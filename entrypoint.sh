#!/bin/bash

set -e

export PATH=/srv/bin:$PATH

if [ "$1" != 'sh' ]; then
	set -- phpacto "$@"
fi

if [ "$1" = 'phpacto' ]; then
    case $2 in
        server_mock)
            cat BANNER.txt
            echo

            php -S 0.0.0.0:8000 bin/server_mock.php
            exit
            ;;

        proxy_recorder)
            cat BANNER.txt
            echo

            php -S 0.0.0.0:8000 bin/proxy_recorder.php
            exit
            ;;

        validate)
            # validate has its own banner
            ;;

        *)
            echo $"Commands: (server_mock|proxy_recorder|validate)"
            exit 1
    esac
fi

exec "$@"
