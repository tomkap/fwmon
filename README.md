# fwmon

## Info
**Firewall Monitoring via MikroTik RouterOS API**, based on [routeros-api](https://github.com/BenMenking/routeros-api)

:worried: [tomkap](https://github.com/tomkap): devops, coding, API debug

:neckbeard: [niemal](https://github.com/niemal): coding, UI


Live demo: [here](https://pebkac.gr/fwmon/)

## Installation
### Requirements
* HTTP Server (Apache2, nginx)
* PHP v5.x

### Steps
Clone it
```
$ git clone https://github.com/tomkap/fwmon.git
```

Insert your MikroTik credentials and rename config file
```
$ cd fwmon/
$ $EDITOR template_config.json
$ mv template_config.json config.json
```
Enjoy!
