#!/bin/sh

URL="$1"
if [ -z $URL]; then
    echo "Please retry with valid url | Example : /aria2add http://file.server.com/file.mp4"

else
    curl $(uci get aria2.main._rpc_url) -X POST --data '{"jsonrpc": "2.0","id":"foo", "method": "aria2.addUri", "params":[["'"$URL"'"]]}'
    echo "
Task added to Aria2 - Check download status /aria2stats"
fi
