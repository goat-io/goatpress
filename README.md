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
    <a href="https://docs.goatlab.io/#/0.4.x/fluent/fluent"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://github.com/goat-io/fluent/repo">View Demo</a>
    ·
    <a href="https://github.com/goat-io/fluent/issues">Report Bug</a>
    ·
    <a href="https://github.com/goat-io/fluent/issues">Request Feature</a>
  </p>
</p>

# Goatpress - Superchaged Wordpress/Woocommerce (version: 0.1.0)

Base image to create Scalable Wordpress and Woocommerce sites based on [Openbridge](https://github.com/openbridge/nginx)'s and [Khromov](https://github.com/khromov/alpine-nginx-php8)'s work

## Create your own site

You can either clone this project, or just use one of the docker images on your own project.

```bash
  npm run deploy
```
## Local Development

#### Starting containers

```
sudo make start
```

#### Remove containers

```
sudo make stop
```

### Setting Project URL

Go to wp-app/.env file and set the variables

```
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

## Deploy to GCP Cloud Run

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
## Plugins 
https://wordpress.org/plugins/webp-converter-for-media/

[stars-shield]: https://img.shields.io/github/stars/goat-io/fluent?style=flat-square
[stars-url]: https://github.com/goat-io/fluent/stargazers
[issues-shield]: https://img.shields.io/github/issues/goat-io/fluent?style=flat-square
[issues-url]: https://github.com/goat-io/fluent/issues