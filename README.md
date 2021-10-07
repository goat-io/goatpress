[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![MIT License](https://img.shields.io/apm/l/atomic-design-ui.svg?)](https://github.com/tterb/atomic-design-ui/blob/master/LICENSEs)  

<p align="center">
  <a href="https://github.com/github_username/repo">
       <img src="https://docs.goatlab.io/logo.png" alt="Logo" width="150" height="150">
  </a>

  <h3 align="center">Goatpress</h3>

  <p align="center">
    Scalable base image for Wordpress
    <br />
    <a href="https://github.com/goat-io/goatpress"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://github.com/goat-io/goatpress">View Demo</a>
    ·
    <a href="https://github.com/goat-io/goatpress/issues">Report Bug</a>
    ·
    <a href="https://github.com/goat-io/goatpress/issues">Request Feature</a>
  </p>
</p>

# Goatpress - Superchaged Wordpress/Woocommerce

Base image to create Scalable Wordpress and Woocommerce sites based on [Openbridge](https://github.com/openbridge/nginx) and [Khromov](https://github.com/khromov/alpine-nginx-php8).

Goatpress runs on a single container to facilitate Cloud Run or K8s deployments

## Features (from [Openbridge](https://github.com/openbridge/nginx))

The image includes configuration enhancements for;

* PHP 8.0 and PHP-FPM
* NGINX 1.19.9
* Pre-configured NEW RELIC installation
* Reverse Proxy
* SEO optimizations
* Customizable configurations
* SSL with support for Lets Encrypt SSL certificates
* Mime-type based caching
* Redis LRU cache
* Fastcgi cache
* Proxy cache
* tmpfs file cache
* Brotli and Gzip compression
* Redirects for moved content
* [Security & Bot Protection](https://github.com/mitchellkrogza/nginx-ultimate-bad-bot-blocker)
* Monitoring processes, ports, permissions... with Monit
* Standardized UID/GID and Permissions (www-data)
* Support GeoIP
* Rate limited connections to slow down attackers
* CDN support
* Cache purge
* [High performance PHP-FPM](https://hub.docker.com/r/openbridge/ob_php-fpm/) for [blazing fast Wordpress installs](https://github.com/openbridge/wordpress)

## Create your own site

Clone this project, create an env file and execute the following command in your terminal

```bash
  make start
```

## Local Development

This project has a full WP copy and some useful basic plugins that you can activate or remove as you like.

### Create ENV file

  Start the development by creating an env file based on the provided env-example file. Set all the required env variables to suit your configuration

### Starting containers

 This command will start both a MYSQL database and a Redis instance so you can test things locally

```bash
 make start
```

### Remove containers

```bash
sudo make stop
```

### Setting Project URL

Go to wp-app/.env file and set the variables

```config
WORDPRESS_WP_SITEURL=https://<my-url>
WORDPRESS_WP_API_URL=https://<my-url>
```

## Creating database dumps

```
sudo make backup
```


## Developing a Theme

Configure the volume to load the theme in the container in the docker-compose.yml

```
volumes:
  - ./theme-name/trunk/:/var/www/html/wp-content/themes/theme-name
```

## Developing a Plugin

Configure the volume to load the plugin in the container in the docker-compose.yml

```
volumes:
  - ./plugin-name/trunk/:/var/www/html/wp-content/plugins/plugin-name
```

### Compiling the images

You can (should) rename the resulting image name/tag by editing the docker-compose.yml file on the woo_prod service

```
  woo_prod:
    build:
      context: .
      dockerfile: ./docker/nginx/Dockerfile
    image: gcr.io/fluent-cd90c/woocommerce:1.19.9
```

```bash
make build
```

## Redis Cache

The image is setup to use Redis as a reverse proxy LRU cache. The current cache settings reflect a balance of performance, optimization and striving for efficiencies with little resource consumption as possible. There are three main configs for Redis:
```nginx
/redis.d/location.conf
/redis.d/cache.conf
/upstream.d/redis.conf
```
### What is a Redis LRU cache?
When an http client requests a web page Nginx looks for the corresponding cached object in Redis. If the object is in redis, nginx serves it. If the object is not in Redis, Nginx requests a backend that generates the page and gives it back to Nginx. Then, Nginx put it in Redis and serves it. All cached objects in Redis have a configurable TTL with a default of `15s`.

## Local Development SSL Certs
If you set `NGINX_DEV_INSTALL=true` it will install a self-signed SSL certs for you. If you already have mounted dev certs, it will not install them as it assumes you want to use those. Here is the code that does this when you set set `NGINX_DEV_INSTALL=true`:

```bash
if [[ ! -f /etc/letsencrypt/live/${NGINX_SERVER_NAME}/privkey.pem ]] || [[ ! -f /etc/letsencrypt/live/${NGINX_SERVER_NAME}/fullchain.pem ]]; then

  echo "OK: Installing development SSL certificates..."
  mkdir -p /etc/letsencrypt/live/${NGINX_SERVER_NAME}

  /usr/bin/env bash -c "openssl req -new -newkey rsa:4096 -days 365 -nodes -x509 -subj /C=US/ST=MA/L=Boston/O=ACMECORP/CN=${NGINX_SERVER_NAME} -keyout /etc/letsencrypt/live/${NGINX_SERVER_NAME}/privkey.pem -out /etc/letsencrypt/live/${NGINX_SERVER_NAME}/fullchain.pem"

  cp /etc/letsencrypt/live/${NGINX_SERVER_NAME}/fullchain.pem  /etc/letsencrypt/live/${NGINX_SERVER_NAME}/chain.pem

else
  echo "INFO: SSL files already exist. Not installing dev certs."
fi
```
When use self-signed certs you will likely see warnings in the logs like this:

```bash
2018/10/25 18:23:53 [warn] 1#1: "ssl_stapling" ignored, no OCSP responder URL in the certificate "/etc/letsencrypt/live/localhost/fullchain.pem"
nginx: [warn] "ssl_stapling" ignored, no OCSP responder URL in the certificate "/etc/letsencrypt/live/localhost/fullchain.pem"
```
This is because nginx is attempting to use `ssl_stapling` which will not function correctly for self-signed certs. You can ignore these warnings in this case. However, if the same warning happens with real certs then there is a different problem with the SSL cert(s).



## Activating Bot Protection
If you want to activate bot protection, you need to set an environment variable called `NGINX_BAD_BOTS` to `true`.
```bash
NGINX_BAD_BOTS=true
```
If you do not set this variable, then do not include it or set the value to `false`
```bash
NGINX_BAD_BOTS=false
```

### Verify Your Bot Protection
Run the following commands line by line inside a terminal on another linux machine against your own domain name.

**Substitute yourdomain.com in the examples below with your REAL domain name:**

`curl -A "googlebot" http://yourdomain.com`

Should respond with 200 OK

`curl -A "80legs" http://yourdomain.com`

`curl -A "masscan" http://yourdomain.com`

Should respond with: curl: (52) Empty reply from server

`curl -I http://yourdomain.com -e http://100dollars-seo.com`

`curl -I http://yourdomain.com -e http://zx6.ru`

Should respond with: curl: (52) Empty reply from server

The Bot Blocker is now WORKING and PROTECTING your sites!
# Content Delivery Network
If you want to activate CDN for assets like images, you can set your location to redirect those requests to your CDN by setting the NGINX_CDN_HOST env variable:
```nginx
location ~* \.(gif|png|jpg|jpeg|svg)$ {
   return  301 ${NGINX_CDN_HOST}$request_uri;
}
```
This assumes you have a CDN distribution setup and the assets published there. 

# Logs
Logs are currently sent to `stdout` and `stderr`. This keeps the deployed service light. You will likely want to dispatch logs to a service like Amazon Cloudwatch. This will allow you to setup alerts and triggers to perform tasks based on container activity without needing to keep logs local and chew up disk space.

However, if you want to change this behavior, simply edit the `Dockerfile` to suit your needs:

```
&& ln -sf /dev/stdout ${LOG_PREFIX}/access.log \
&& ln -sf /dev/stderr ${LOG_PREFIX}/error.log \
&& ln -sf /dev/stdout ${LOG_PREFIX}/blocked.log

```

# Deploy to GCP Cloud Run

### Login to Google Cloud

gcloud auth login

gcloud auth configure-docker

## Deploy app on Kubernetes cluster (Soon)

If no certificates are provided, autosign your own and pass it to Kubernetes as a secret.

```
openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout ${KEY_FILE} -out ${CERT_FILE} -subj "/CN=${HOST}/O=${HOST}"`
kubectl create secret tls tls-certificate --key ./certs/tls-key.key --cert ./certs/tls-cert.crt
```

First, store your Gitlab credentials on a Kubernetes Secret.

```
kubectl create secret generic regcred \
    --from-file=.dockerconfigjson=<path/to/.docker/config.json> \
    --type=kubernetes.io/dockerconfigjson


kubectl create secret generic regcred --from-file=.dockerconfigjson=recred.json --type=kubernetes.io/dockerconfigjson
```

Then, read your .env file as a Kubernetes configMap.

```
kubectl create configmap wp-config --from-env-file=<path/to/.env>
```

Last but not least, deploy your service from the provided configuration files.

```
cd kubernetes/kubeFiles
kubectl apply -f mysql-deployment.yaml ### ONLY IF THERE'S NO DATABASE ALREADY UP
kubectl apply -f wordpress-deployment.yaml
kubectl apply -f nginx-ingress.yaml
```

### Deployment on GKE

To deploy on Google Cloud services once kubectl is configured to use our cluster (and Helm is installed locally), run the following commands BEFORE applying the deployment files to set up Helm/Tiller in the cluster:

```
kubectl create serviceaccount --namespace kube-system tiller
kubectl create clusterrolebinding tiller-cluster-rule --clusterrole=cluster-admin --serviceaccount=kube-system:tiller
helm init --service-account tiller
```

Then, install the Nginx Ingress Controller for use within our application.

```
helm install --name nginx-ingress stable/nginx-ingress --set rbac.create=true
```

Get the external IP from the following command, as that is our url for the app. Use the IP address to configure extra files if needed.

```
kubectl get service nginx-ingress-controller
```

[stars-shield]: https://img.shields.io/github/stars/goat-io/goatpress?style=flat-square
[stars-url]: https://github.com/goat-io/goatpress/stargazers
[issues-shield]: https://img.shields.io/github/issues/goat-io/goatpress?style=flat-square
[issues-url]: https://github.com/goat-io/goatpress/issues