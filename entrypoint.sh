#!/bin/bash

set -e

export PATH=/srv/bin:$PATH

if [ "$1" != 'sh' ]; then
	set -- phpacto "$@"
fi

if [ "$1" = 'phpacto' ]; then
    case $2 in
        server_mock)
            cat BANNER
            echo

            php -S 0.0.0.0:8000 bin/server_mock.php
            exit
            ;;

        mock_proxy_recorder)
            cat BANNER
            echo

            php -S 0.0.0.0:8000 bin/mock_proxy_recorder.php
            exit
            ;;

        validate)
            # validate has its own banner
            ;;

        *)
            echo $"Commands: (validate|server_mock|mock_proxy_recorder)"
            exit 1
    esac
fi

exec "$@"
