#!/bin/sh
curl $(uci get aria2.main._rpc_url) -X POST --data '{"jsonrpc": "2.0","id":"foo", "method": "aria2.unpauseAll", "params":[]}' > /dev/null 2>&1
echo "All Download Resumed"