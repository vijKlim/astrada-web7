FROM node:15-alpine

RUN apk update && apk add --no-cache git

RUN npm install -g pm2@^4.2

WORKDIR /srv/astrada

COPY package.json /srv/astrada
COPY package-lock.json /srv/astrada

COPY docker/nodejs/start.sh /
COPY docker/nodejs/run-tests.sh /

RUN chmod +x /start.sh
RUN chmod +x /run-tests.sh

ENTRYPOINT ["/start.sh"]
