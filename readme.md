<p align="center"><img width="200" src="images/logo.png"></p>

## Introduction

Valet+Containers is a development environment for macOS. No Vagrant, no `/etc/hosts` file.

### Valet+ vs. Valet+Containers

Valet+Containers is a fork of [Valet+](https://github.com/weprovide/valet-plus). Valet+Containers uses Docker Desktop
for running ancillary services (MySql, ElastricSearch, RabbitMQ, Redis). The primary for this was avoiding the need of
switching Java versions on the host when switching ElasticSearch versions.

Valet+ commands work as outlined in the [Valet+ docs](https://github.com/weprovide/valet-plus/blob/master/readme.md). The service versions started by default are dictated based on my
needs and are not the latest and greatest. You can specify the service version when running `valet start {service}`.

#### Starting/Stopping Specific Service Versions
Service versions can be specified when starting the services with valet ex:
```
valet start mysql:5.7
valet start elasticsearch:6
```
***Important***
The version specified when starting the service must also be specified when stopping the service.

Named volumes are mounted for ElasticSearch and MySql services. Each service version has a unique volume.

### M2 Varnish Support
The domain is optional when running the command from a project directory.  
Enable: `valet fpc-on [domain]`  
Disable: `valet fpc-off [domain]`
