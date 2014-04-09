#!/bin/sh

command -v php >/dev/null 2>/dev/null || { echo "Cannot find php" >&2; exit 1; }

if [ -z "$1" ]; then PORT=8000; else PORT=$1; shift; fi

DIR=`cd $(dirname $0); pwd`

cd $DIR/..

php -S localhost:$PORT bin/is_router.php
