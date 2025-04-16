#!/bin/sh
env
rm -rf /var/lib/proxysql/proxysql.db
envsubst < /etc/proxysql.cnf.template > /etc/proxysql.cnf
echo ----------------------------------------------
cat /etc/proxysql.cnf
echo ----------------------------------------------
exec proxysql -f -c /etc/proxysql.cnf --idle-threads -D /var/lib/proxysql
