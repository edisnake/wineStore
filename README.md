## Winery Challenge

**How to install and run the application**

Clone the git repository

    git clone https://github.com/edisnake/wineStore.git


* For non-Docker version
    
    * Make sure your local RabbitMQ setup is ok in environment variable MESSENGER_TRANSPORT_DSN which is in the wineStore/.env file   
    * In your CLI go to wineStore folder and run following commands:
    
            composer install
            php bin/console doctrine:migrations:migrate
            php bin/console doctrine:fixtures:load
            php bin/console server:run

        Now the App can be found in **http://127.0.0.1:8000/**

* Docker version

    * In your CLI go to wineStore/docker folder
    * start docker running 

            docker-compose up -d

        Now the App can be found in your docker IP address i.e. **http://192.168.99.100/**

**Consuming queued messages**

* Non-Docker version

    Go to the wineStore folder and run this command
    
        php bin/console messenger:consume-messages

* Docker version

    Go to the wineStore/docker folder and run this command

        docker exec -it php bash
    
    Once you're in the CLI run this command

        php bin/console messenger:consume-messages

**Running Tests**

Go to the wineStore folder and run this command

    php bin/phpunit tests/


**What has been done**

* Provide Docker Containers

* Import and Store Feed RSS into the App built in Symfony 4

* Implement Symfony 4 Message component

* Implement RabbitMQ queuing system for random orders

* Show Flash messages

* Unit tests with 100% coverage in the Service layer

* CRUD Wine Feed feature to ease wine feed handling

