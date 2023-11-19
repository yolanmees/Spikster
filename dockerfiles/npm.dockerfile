FROM node:16.20-slim

WORKDIR /var/www/html
COPY ../package.json ../package-lock.json ../webpack.mix.js ./
COPY ../resources/ /var/www/html/resources

RUN npm install

EXPOSE 9000

CMD npm run dev