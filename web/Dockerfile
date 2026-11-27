# syntax=docker/dockerfile:1

# --- Compilation -----------------------------------------------------------
FROM node:22-alpine AS build
WORKDIR /src
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# --- Service statique ------------------------------------------------------
FROM nginx:1-alpine AS runtime
COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /src/dist /usr/share/nginx/html
EXPOSE 80
# Sert uniquement le front. `/api` est routé vers le conteneur `chiefplanner-api`
# par le reverse proxy (Nginx Proxy Manager), pas par ce nginx.
