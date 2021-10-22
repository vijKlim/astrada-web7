#!/bin/sh

cd /srv/astrada

# https://docs.cypress.io/guides/getting-started/installing-cypress.html#Skipping-installation
CYPRESS_INSTALL_BINARY=0 npm install

node_modules/.bin/encore dev-server --host 0.0.0.0 --public http://rxz.test:8080 --port 8080
