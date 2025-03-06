
## Prepare
- Tidb cloud
  - https://tidbcloud.com/

- Datadog account
eg.
```
DD_API_KEY=**
DD_SITE=datadoghq.com
```
- Tpcc
```
tiup bench tpcc prepare -H$tidbhost -P$tidbport -D test -Uroot -p$tidbpass --warehouses 2 --threads 2
```

- OS Tuning (for high QPS scenario)
```
sysctl -w net.ipv4.ip_local_port_range="1024 65535"
sysctl -w net.ipv4.tcp_tw_reuse=1
sysctl -w net.ipv4.tcp_fin_timeout=10
sysctl -w net.netfilter.nf_conntrack_max=1048576
sysctl -w net.netfilter.nf_conntrack_buckets=262144

ulimit -n 1048576
echo "* soft nofile 1048576" >> /etc/security/limits.conf
echo "* hard nofile 1048576" >> /etc/security/limits.conf

# append to /etc/systemd/system.conf 和 /etc/systemd/user.conf
DefaultLimitNOFILE=1048576
# restart  systemd-logind：
systemctl restart systemd-logind

# DNS cache for systemd-resolved

sudo systemctl enable systemd-resolved
sudo systemctl restart systemd-resolved

vim /etc/systemd/resolved.conf
[Resolve]
Cache=yes
DNS=8.8.8.8 1.1.1.1
DNSStubListener=yes

sudo systemctl restart systemd-resolved

```
- Workload
```
git clone https://github.com/dulao5/tpcc-sidecar-workload-with-monitor.git

$ git diff
diff --git a/docker-compose.yml b/docker-compose.yml
index d76fe77..279d734 100644
--- a/docker-compose.yml
+++ b/docker-compose.yml
@@ -9,10 +9,10 @@ services:
           - phpapp
         ipv4_address: 10.10.10.10
     environment:
-      - DB_HOST=docker.for.mac.host.internal
-      - DB_PORT=**
-      - DB_USER=smallworkload
-      - DB_PASS=hogeTODO
+      - DB_HOST=**
+      - DB_PORT=**
+      - DB_USER=root
+      - DB_PASS=**
       - DB_NAME=test
   nginx:
     #platform: linux/amd64
@@ -59,8 +59,9 @@ services:
     container_name: dd-agent
     image: datadog/agent:7
     environment:
-      - DD_API_KEY=ここに自分のAPIキーを書く
+      - DD_API_KEY=706fff9a5405849502d07dfb9b977c9e
       - DD_AGENT_MAJOR_VERSION=7
+      - DD_SITE=datadoghq.com
       - DD_APM_ENABLED=true
       - DD_LOGS_ENABLED=true
       - DD_LOGS_CONFIG_CONTAINER_COLLECT_ALL=true
```
- Run workload
```
docker-compose exec -it gatling gatling
```


