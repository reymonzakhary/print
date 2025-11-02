FROM node:16

ENV APP_ROOT /www

RUN mkdir ${APP_ROOT}
WORKDIR ${APP_ROOT}

EXPOSE 3000
ENV HOST 0.0.0.0

CMD [ "npm", "run dev" ]