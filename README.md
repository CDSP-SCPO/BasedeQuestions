# varQuest - Base de Questions 
#### Quetelet ProGeDo Diffusion

Varquest lets you search in question texts, response category codes and labels, variable names and label from questionnaires. It is built on DDI, Apache Solr, Zend Framework, PHP 5, MySQL jQuery, etc. 

http://bdq.reseau-quetelet.cnrs.fr/ 


## Docker setup

Due to old and unsupported stack used in this project, all is rebuilded in Docker images to reduce the possible attack scope.

Docker enables isolated install and execution of software stacks, which can be an easy way to install varQuest.

Follow [Docker install instructions](https://docs.docker.com/installation/) to install Docker on your machine.

Once you've Docker installed and running, [install Docker Compose](https://docs.docker.com/compose/install/) to set up and orchestrate Hyphe services in a single line.



## Docker images

I've created a PHP-FPM image based on PHP 5.3, that contains source code and is available on Docker Hub: 
* https://hub.docker.com/r/cdspscpo/quetelet-bdq/

We also use an Apache Solr 1.4 image based on this work: https://github.com/sebstinkeste/solr-1.4
* https://hub.docker.com/r/jrisp/solr-1.4/

Finally, we use official Nginx and MySQL images for database and frontend:
* https://hub.docker.com/_/nginx/
* https://hub.docker.com/_/mysql/


## Get this repo

Clone this repo to you server: 
```bash
git clone https://github.com/CDSP-SCPO/BasedeQuestions.git
```

### Pull official image from Docker Store (recommended way)

```bash
docker-compose pull
```

### Or build your own images from the source code

```bash
docker-compose build
```

It will take a couple of minutes to download or build everything.

### Copy data

Copy the `quetelet-data` folder in the root directory of this repository cloned before.

### Create and run containers

Once done, you can run Hyphe containers with this command:

```bash
docker-compose up
```

You can use `-d` option to run containers in the background. 

Once the services are ready, you can access the frontend interface by connecting on `localhost` or the Docker host IP address:

```bash
open http://localhost
```

It could be useful to see the containers logs, you can do it with:

```bash
docker-compose logs
```

Use `-f` option to follow the logs output.
