# Stage 1: Build Frontend Assets
FROM node:22-alpine AS builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js postcss.config.js tailwind.config.js ./

RUN npm run build

# Stage 2: Nginx Production Image
FROM nginx:alpine

# Copy custom Nginx configuration
COPY ./docker/production/nginx/nginx.conf /etc/nginx/conf.d/default.conf

# Copy public directory and compiled assets from the builder stage
COPY --from=builder /app/public /var/www/html/public

# Setup storage directory as volume mountpoint
RUN mkdir -p /var/www/html/storage/app/public

WORKDIR /var/www/html/public

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
