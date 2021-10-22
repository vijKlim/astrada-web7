FROM node:15-alpine

RUN apk update && apk upgrade && \
    apk add --no-cache bash git openssh

WORKDIR /srv/astrada

COPY package.json /srv/astrada
#COPY package-lock.json /srv/astrada

COPY docker/webpack/start.sh /

RUN chmod +x /start.sh

ENTRYPOINT ["/start.sh"]
