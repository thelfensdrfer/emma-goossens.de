FROM node:18 AS static

RUN mkdir /app

WORKDIR /app

COPY gatsby-browser.js gatsby-config.js package.json tailwind.config.js yarn.lock ./
COPY src/ ./src/

RUN yarn install
RUN ./node_modules/.bin/gatsby telemetry --disable
RUN ls -la
RUN yarn run build

FROM nginx:1.21-alpine

COPY --from=static /app/public /usr/share/nginx/html

CMD ["nginx", "-g", "daemon off;"]
