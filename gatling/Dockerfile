FROM openjdk:11
ENV LANG C.UTF-8

RUN apt-get update -y \
    && apt-get install -y wget libarchive-tools

RUN mkdir /workdir && cd /workdir
WORKDIR /workdir

# old : 3.3.1
# support http.requestTimeout v3.6.1
# latest: 3.7.6

RUN wget -qO- https://repo1.maven.org/maven2/io/gatling/highcharts/gatling-charts-highcharts-bundle/3.5.1/gatling-charts-highcharts-bundle-3.5.1-bundle.zip \
    | bsdtar -xvf-
ENV GATLING_HOME=/workdir/gatling-charts-highcharts-bundle-3.5.1
RUN rm -rf /workdir/gatling-charts-highcharts-bundle-3.5.1/user-files/simulations/computerdatabase/advanced
RUN ln -s /workdir/gatling-charts-highcharts-bundle-3.5.1/bin/gatling.sh /usr/local/bin/gatling && chmod a+x /usr/local/bin/gatling

